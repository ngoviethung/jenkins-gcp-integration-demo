@if ($crud->hasAccess('bulkVip') && $crud->get('list.bulkActions'))
    <div class="col-2">
        <div class="row>" style="height: 33px">
            <a href="javascript:void(0)" onclick="bulkSetVip(this)" class="btn btn-sm btn-secondary bulk-button assign col-7"><i class="fa fa-clone"></i> Set Vip</a>
            <select id="vip" name="vip" class="float-right form-control col-4" style="height: 100%;">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

    </div>
@endif

@push('after_scripts')
    <script type="text/javascript">
        if (typeof bulkSetVip != 'function') {

            function bulkSetVip(button) {
                var Vip = $('#vip').val();

                if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
                {
                    new Noty({
                        type: "warning",
                        text: "<strong>{{ trans('backpack::crud.bulk_no_entries_selected_title') }}</strong><br>{{ trans('backpack::crud.bulk_no_entries_selected_message') }}"
                    }).show();

                    return;
                }

                var message = "Are you sure you want to set Vip these :number entries?";
                message = message.replace(":number", crud.checkedItems.length);

                // show confirm message
                swal({
                    title: "{{ trans('backpack::base.warning') }}",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "{{ trans('backpack::crud.cancel') }}",
                            value: null,
                            visible: true,
                            className: "bg-secondary",
                            closeModal: true,
                        },
                        delete: {
                            text: "Set",
                            value: true,
                            visible: true,
                            className: "bg-primary",
                        }
                    },
                }).then((value) => {
                    if (value) {
                        var ajax_calls = [];
                        var Vip_route = "{{ url($crud->route) }}/bulk-vip";



                        // submit an AJAX delete call
                        $.ajax({
                            url: Vip_route,
                            type: 'POST',
                            data: { entries: crud.checkedItems, vip: Vip},
                            success: function(result) {
                                // Show an alert with the result
                                new Noty({
                                    type: "success",
                                    text: "<strong>Entries Set</strong><br>"+crud.checkedItems.length+" new entries have been added."
                                }).show();

                                crud.checkedItems = [];
                                crud.table.ajax.reload();
                            },
                            error: function(result) {
                                // Show an alert with the result
                                new Noty({
                                    type: "danger",
                                    text: "<strong>Set failed</strong><br>One or more entries could not be created. Please try again."
                                }).show();
                            }
                        });
                    }
                });
            }
        }
    </script>
    <style>
        .bulk-button.assign {
            background: #467fcf;
            color:#fff;
        }

        .bulk-button.assign.disabled {
            background: black;
        }
    </style>
@endpush