<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp;

class nso extends Controller
{
    private function nso_get_data($url)
    {
        $client = new GuzzleHttp\Client();

        $res = $client->request('GET', $_ENV['NSO_URL'] . $url,
            [
                'http_errors' => false,
                'auth' => [$_ENV['NSO_USER'], $_ENV['NSO_PASS']],
                'headers' => [
                    'Accept' => 'application/vnd.yang.data+json'
                ]
            ]);

        if ($res->getStatusCode() == 200) {
            return json_decode($res->getBody(), true);
        } else {
            return [];
        }
    }

    private function nso_post_data($url, $data)
    {
        $client = new GuzzleHttp\Client();

        $result = $client->request('POST', $_ENV['NSO_URL'] . $url,
            [
                'http_errors' => false,
                'body' => $data,
                'auth' => [$_ENV['NSO_USER'], $_ENV['NSO_PASS']],
                'headers' => [
                    'Content-Type' => 'application/vnd.yang.data+json'
                ]
            ]);

        return $result->getStatusCode();
    }

    private function nso_delete($url)
    {
        $client = new GuzzleHttp\Client();

        $result = $client->request('DELETE', $_ENV['NSO_URL'] . $url,
            [
                'http_errors' => false,
                'auth' => [$_ENV['NSO_USER'], $_ENV['NSO_PASS']]
            ]);

        if ($result->getStatusCode() == 204) {
            return response('Successfully called NSO API: ' . $result->getBody(), 200);
        } else {
            return response('NSO API call failed: ' . $result->getBody(), 500);
        }
    }

    private function os_get_segments()
    {
        $client = new GuzzleHttp\Client();

        $auth_request = <<<EOL
{
    "auth": {
        "identity": {
            "methods": [
                "password"
            ],
            "password": {
                "user": {
                    "domain": {
                        "name": "{$_ENV['OS_DOMAIN']}"
                    },
                    "name": "{$_ENV['OS_USER']}",
                    "password": "{$_ENV['OS_PASS']}"
                }
            }
        },
        "scope": {
            "project": {
                "domain": {
                    "name": "{$_ENV['OS_DOMAIN']}"
                },
                "name": "{$_ENV['OS_PROJECT']}"
            }
        }
    }
}
EOL;

        $res = $client->request('POST', $_ENV['OS_AUTH_URL'] . "/auth/tokens",
            [
                'http_errors' => false,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => $auth_request
            ]);

        if ($res->getStatusCode() != 201) {
            die("OpenStack authentication error. " . $res->getBody());
        } else {
            $auth_token = $res->getHeader("X-Subject-Token")[0];
        }

        $res = $client->request('GET', $_ENV['OS_NEUTRON_URL'] . "/networks",
            [
                'http_errors' => false,
                'headers' => [
                    'X-Auth-Token' => $auth_token
                ]
            ]);

        if ($res->getStatusCode() != 200) {
            die("OpenStack Neutron API error. " . $res->getBody());
        }

        $segment_ids = [];

        foreach (json_decode($res->getBody(), true)['networks'] as $network) {
            $segment_ids[$network['name']] = $network['provider:segmentation_id'];
        }

        return $segment_ids;
    }


    public function list_tenants()
    {
        $tenants = [];

        $url = '/api/running/devices/device/' . $_ENV['NSO_ESC'] . '/config/esc_datamodel/tenants';

        foreach ($this->nso_get_data($url)['esc:tenants']['tenant'] as $tenant)
            if ($tenant['name'] != 'admin') $tenants[] = $tenant['name'];

        return view('tenants', ['tenants' => $tenants]);
    }

    public function get_tenant($tenant)
    {
        $networks = [];
        $deployments = [];
        $services = [];

        $segments = $this->os_get_segments();

        $tenant_data = $this->nso_get_data('/api/running/devices/device/' . $_ENV['NSO_ESC']
            . '/config/esc_datamodel/tenants/tenant/' . $tenant);
        $nsr_data = $this->nso_get_data('/api/running/nfvo/nsr?deep');

        if (array_key_exists('networks', $tenant_data['esc:tenant'])) {
            foreach ($tenant_data['esc:tenant']['networks']['network'] as $network) {
                $networks[$network['name']] = $segments[$network['name']];
            }
        }

        if (array_key_exists('deployments', $tenant_data['esc:tenant'])) {
            foreach ($tenant_data['esc:tenant']['deployments']['deployment'] as $deployment) {
                foreach ($nsr_data['tailf-nfvo:nsr']['tailf-nfvo-esc:esc']['nsr'] as $nsr) {
                    if ($nsr['deployment-name'] == $deployment['name']) {
                        $deployments[$deployment['name']]['nsd'] = $nsr['nsd'];
                        foreach ($nsr['service-access-point'] as $sap) {
                            if ($sap['id'] == 'inside')
                                $deployments[$deployment['name']]['sap'] = $sap['vlr'];
                        }
                    }
                }
            }
        }

        foreach (array_diff(scandir(__DIR__ . '/../../../storage/app/service-templates'), array('..', '.')) as $svc) {
            $services[] = str_replace(".json", "", $svc);
        }

        return view('tenant', ['networks' => $networks, 'deployments' => $deployments,
            'tenant' => $tenant, 'services' => $services]);
    }

    public function delete_network($tenant, $network)
    {
        $url = "/api/running/devices/device/" . $_ENV['NSO_ESC'] .
            "/config/esc:esc_datamodel/tenants/tenant/$tenant/" .
            "networks/network/$network";

        return $this->nso_delete($url);
    }

    public function delete_tenant($tenant)
    {
        $url = "/api/running/devices/device/" . $_ENV['NSO_ESC'] .
            "/config/esc:esc_datamodel/tenants/tenant/$tenant";

        return $this->nso_delete($url);
    }

    public function delete_service($tenant, $service)
    {
        $url = "/api/running/nfvo/nsr/esc/nsr/$tenant,$service,{$_ENV['NSO_ESC']}";

        return $this->nso_delete($url);
    }

    public function create_tenant($tenant)
    {
        $url = "/api/running/devices/device/" . $_ENV['NSO_ESC'] .
            "/config/esc:esc_datamodel/tenants";

        $data = '{ "tenant": [{ "name": "' . $tenant . '" }] }';

        return response('', $this->nso_post_data($url, $data));
    }

    public function create_network(Request $request, $tenant, $network)
    {
        $url = "/api/running/devices/device/" . $_ENV['NSO_ESC'] .
            "/config/esc:esc_datamodel/tenants/tenant/$tenant/" .
            "networks";

        $req = json_decode($request->getContent(false), true);

        if (is_array($req) && array_key_exists('segment_id', $req))
            $segment_id = $req['segment_id']; else $segment_id = '';

        $data = '{ "network": [{ "name": "' . $network . '", "shared": false,';
        if ($segment_id != '')
            $data = $data . '"provider_physical_network": "' . $_ENV['OS_PHYSNET'] .
                '", "provider_network_type": "' . $_ENV['OS_NET_TYPE'] .
                '", "provider_segmentation_id": ' . $segment_id . ',';
        $data = $data . '"subnet": [{ "name": "' . $network . '", "ipversion": "ipv4", "dhcp": false, "address": "' .
            $_ENV['OS_DEF_NET'] . '", "netmask": "' . $_ENV['OS_DEF_MASK'] . '", "no_gateway": true }] }]}';

        return response('', $this->nso_post_data($url, $data));
    }

    public function create_service(Request $request, $tenant, $servicename)
    {
        $req = json_decode($request->getContent(false), true);

        if (is_array($req) && array_key_exists('service', $req) && array_key_exists('network', $req)) {
            $servicetype = $req['service'];
            $network = $req['network'];
        } else {
            return response('', 400);
        }

        $template = __DIR__ . '/../../../storage/app/service-templates/' . $servicetype . '.json';

        if (!file_exists($template))
            return response('', 400);

        $query = file_get_contents($template);

        $query = str_replace('@TENANT@', $tenant, $query);
        $query = str_replace('@SERVICENAME@', $servicename, $query);
        $query = str_replace('@NETWORK@', $network, $query);

        $vlr_url = 'api/running/nfvo/vlr/esc';
        $vlr_data = '{ "vlr": [ { "id": "' . $network . '" } ] }';

        $vlr_res = $this->nso_post_data($vlr_url, $vlr_data);
        if ($vlr_res != 201 && $vlr_res != 409) {
            return response('', 500);
        }

        $url = '/api/running/nfvo/nsr/esc';
        return response('', $this->nso_post_data($url, $query));
    }
}