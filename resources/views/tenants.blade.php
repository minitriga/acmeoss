@extends('layout')

@section('title', 'Клиенты')

@section('content')
    <script>
        var $tenant;

        $(document).ready( function() {
            $tenant = $('#tenant');
        });

        function confirmTenantDelete(tenant) {
            bootbox.confirm({
                message: 'Вы действительно хотите удалить клиента ' + tenant + '?',
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
                            url: '/api/tenants/' + tenant,
                            type: 'DELETE',
                            success: function () {
                                $('#tr_' + tenant).remove();
                                $.notify({
                                    message: 'Клиент ' + tenant + ' удален.'
                                }, {
                                    type: 'success'
                                });
                            },
                            error: function () {
                                $.notify({
                                    message: 'Ошибка при удалении клиента ' + tenant + '.'
                                }, {
                                    type: 'danger'
                                });
                            }
                        });
                    }
                }
            });
        }

        function addTenant(tenant) {
            $.ajax({
                url: '/api/tenants/' + tenant,
                type: 'POST',
                success: function () {
                    addTenantToTable(tenant);
                    $.notify({
                        message: 'Клиент ' + tenant + ' добавлен.'
                    }, {
                        type: 'success'
                    });
                },
                error: function () {
                    $.notify({
                        message: 'Ошибка при добавлении клиента ' + tenant + '.'
                    }, {
                        type: 'danger'
                    });
                }
            });
        }

        function addTenantToTable(tenant) {
            $('#tenanttable').append('<tr id=\"tr_' + tenant + '\"><td><a href=\"\/clients\/' + tenant + '\">' +
                    tenant + '<\/a><\/td><td><button type=\"button\" class=\"btn btn-danger\" ' +
                    'onclick=\"confirmTenantDelete(\'' + tenant + '\')\"><span class=\"glyphicon glyphicon-remove\" ' +
                    'aria-hidden=\"true\"><\/span><\/button><\/td><\/tr>');
        }
    </script>
    <table class="table" id="tenanttable">
        <thead>
        <tr>
            <th style="width: 100%">
                Наименоваие клиента
            </th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($tenants as $tenant)
            <script>addTenantToTable('{{$tenant}}');</script>
        @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_addtenant">
        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
        &nbsp;Добавить клиента
    </button>

    <div class="modal fade" id="modal_addtenant" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        Новый клиент
                    </h4>
                </div>
                <div class="modal-body">
                    <form role="form">
                        <div class="form-group">
                            <label for="tenant">Наименование клиента</label>
                            <input type="text" class="form-control" id="tenant" placeholder="Example Company"/>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"
                            onclick="$tenant.val('')">
                        Отмена
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                            onclick="addTenant($tenant.val()); $tenant.val('');">
                        Добавить клиента
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection