@extends('layout')

@section('title')
    Сети и сервисы клиента {{$tenant}}
@endsection

@section('content')
    <script>
        var $networktable;
        var $servicetable;
        var $network;
        var $segment;
        var $servicename;
        var $servicetype;
        var $servicenet;

        $(document).ready(function () {
            $networktable = $('#networktable');
            $servicetable = $('#servicetable');
            $network = $('#network');
            $segment = $('#segment');
            $servicename = $('#servicename');
            $servicetype = $('#servicetype');
            $servicenet = $('#servicenet');
        });

        function confirmNetDelete(network) {
            bootbox.confirm({
                message: 'Вы действительно хотите удалить сеть ' + network + ' клиента {{$tenant}}?',
                buttons: {
                    confirm: {
                        label: 'Удалить',
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: 'Отмена',
                        className: 'btn-success'
                    }
                },
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: '/api/tenants/{{$tenant}}/networks/' + network,
                            type: 'DELETE',
                            success: function (result) {
                                $('#tr_net_' + network).remove();
                                $.notify({
                                    message: 'Сеть ' + network + ' удалена.'
                                }, {
                                    type: 'success'
                                });
                            },
                            error: function (result) {
                                $.notify({
                                    message: 'Ошибка при удалении сети ' + network + '.'
                                }, {
                                    type: 'danger'
                                });
                            }
                        });
                    }
                }
            });
        }

        function confirmDeploymentDelete(deployment) {
            bootbox.confirm({
                message: 'Вы действительно хотите удалить сервис ' + deployment + ' клиента {{$tenant}}?',
                buttons: {
                    confirm: {
                        label: 'Удалить',
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: 'Отмена',
                        className: 'btn-success'
                    }
                },
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: '/api/tenants/{{$tenant}}/services/' + deployment,
                            type: 'DELETE',
                            success: function (result) {
                                $('#tr_dep_' + deployment).remove();
                                $.notify({
                                    message: 'Сервис ' + deployment + ' удален.'
                                }, {
                                    type: 'success'
                                });
                            },
                            error: function (result) {
                                $.notify({
                                    message: 'Ошибка при удалении сервиса ' + deployment + '.'
                                }, {
                                    type: 'danger'
                                });
                            }
                        });
                    }
                }
            });
        }

        function addNetworkToTable(network, segment) {
            $networktable.append('<tr id=\"tr_net_' + network + '\"><td>' + network + '<\/td><td>' +
                    segment + '<\/td><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"confirmNetDelete(' +
                    '\'' + network + '\');\"><span class=\"glyphicon glyphicon-remove\" ' +
                    'aria-hidden=\"true\"><\/span><\/button><\/td><\/tr>');
            $servicenet.append('<option val="' + network + '">' + network + '</option>');
        }

        function addServiceToTable(name, network, service) {
            $servicetable.append('<tr id=\"tr_dep_' + name + '\"><td>' + name + '<\/td><td>' + network +
                    '<\/td><td>' + service + '<\/td><td><button type=\"button\" class=\"btn btn-danger\" ' +
                    'onclick=\"confirmDeploymentDelete(\'' + name + '\');\">' +
                    '<span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"><\/span><\/button><\/td><\/tr>');
        }

        function addNetwork(network, segment) {
            var real_segment = '?';
            var req_data = '';

            if (segment > 1 && segment < 4096) {
                real_segment = segment;
                req_data = '{ \"segment_id\": \"' + segment + '\" }';
            }

            $.ajax({
                url: '/api/tenants/{{$tenant}}/networks/' + network,
                type: 'POST',
                data: req_data,
                success: function (result) {
                    addNetworkToTable(network, real_segment);
                    $.notify({
                        message: 'Сеть ' + network + ' добавлена.'
                    }, {
                        type: 'success'
                    });
                },
                error: function (result) {
                    $.notify({
                        message: 'Ошибка при добавлении сети ' + network + '.'
                    }, {
                        type: 'danger'
                    });
                }
            });
        }

        function addService(name, type, net) {
            var req_data = '{ "service": "' + type + '", "network": "' + net + '" }';

            $.ajax({
                url: '/api/tenants/{{$tenant}}/services/' + name,
                type: 'POST',
                data: req_data,
                success: function (result) {
                    addServiceToTable(name, net, type);
                    $.notify({
                        message: 'Сервис ' + name + ' добавлен.'
                    }, {
                        type: 'success'
                    });
                },
                error: function (result) {
                    $.notify({
                        message: 'Ошибка при добавлении сервиса ' + name + '.'
                    }, {
                        type: 'danger'
                    });
                }
            });
        }

    </script>

    <h3>Сети</h3>
    <table class="table" id="networktable">
        <thead>
        <tr>
            <th style="width: 50%">Сеть</th>
            <th style="width: 50%">Номер VLAN</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($networks as $network => $segment)
            <script>
                $(document).ready(function () {
                    addNetworkToTable('{{$network}}', '{{$segment}}');
                });
            </script>
        @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_addnetwork">
        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
        &nbsp;Добавить сеть
    </button>
    <div class="modal fade" id="modal_addnetwork" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        Новая сеть
                    </h4>
                </div>
                <div class="modal-body">
                    <form role="form">
                        <div class="form-group">
                            <label for="network">Наименование сети</label>
                            <input type="text" class="form-control" id="network" placeholder="Example Network"/>
                        </div>
                        <div class="form-group">
                            <label for="segment">Номер VLAN (необязательно)</label>
                            <input type="text" class="form-control" id="segment" placeholder="0"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"
                            onclick="$network.val(''); $segment.val(''); ">
                        Отмена
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                            onclick="addNetwork($network.val(), $segment.val()); $network.val(''); $segment.val('');">
                        Добавить сеть
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 80px"></div>

    <h3>Сервисы</h3>
    <table class="table" id="servicetable">
        <thead>
        <tr>
            <th style="width: 33%">Наименование</th>
            <th style="width: 33%">Сеть</th>
            <th style="width: 33%">Сервис</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($deployments as $deployment => $service)
            <script>
                $(document).ready(function () {
                    addServiceToTable('{{$deployment}}', '{{$service['sap']}}', '{{$service['nsd']}}');
                });
            </script>
        @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_addservice"
            onclick="$servicetype.val(''); $servicenet.val('');">
        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
        &nbsp;Добавить сервис
    </button>
    <div class="modal fade" id="modal_addservice" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        Новый сервис
                    </h4>
                </div>
                <div class="modal-body">
                    <form role="form">
                        <div class="form-group">
                            <label for="servicename">Наименование сервиса</label>
                            <input type="text" class="form-control" id="servicename" placeholder="Example Service"/>
                        </div>
                        <div class="form-group">
                            <label for="servicetype">Тип сервиса</label>
                            <select class="form-control" id="servicetype">
                                @foreach ($services as $service)
                                    <option val="{{$service}}">{{$service}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="servicenet">Сеть</label>
                            <select class="form-control" id="servicenet"></select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"
                            onclick="$servicename.val(''); $servicetype.val(''); $servicenet.val('');">
                        Отмена
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                            onclick="addService($servicename.val(), $servicetype.val(), $servicenet.val());
                            $servicename.val(''); $servicetype.val(''); $servicenet.val('');">
                        Добавить сервис
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection