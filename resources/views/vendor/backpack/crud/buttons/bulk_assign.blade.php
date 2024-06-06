@if ($crud->hasAccess('bulkAssign') && $crud->get('list.bulkActions'))

    <div class="col-3">
        <div class="row>">
            <a href="javascript:void(0)" onclick="bulkAssignEntries(this)" class="btn btn-sm btn-secondary bulk-button assign"><i class="fa fa-clone"></i> Assign to Topics</a>
            <select id="topic_assigns" name="topic_assigned" class="float-right form-control col-6" multiple>
                @foreach(\App\Models\Topic::all() as $topic)
                    <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif

@push('after_scripts')
    <script type="text/javascript">
        $('#topic_assigns').select2({
            // theme:'bootstrap'
        })

        if (typeof bulkAssignEntries != 'function') {

            function bulkAssignEntries(button) {
                var topics = $('#topic_assigns').val();

                if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0 || topics.length == 0)
                {
                    new Noty({
                        type: "warning",
                        text: "<strong>{{ trans('backpack::crud.bulk_no_entries_selected_title') }}</strong><br>{{ trans('backpack::crud.bulk_no_entries_selected_message') }}"
                    }).show();

                    return;
                }

                var message = "Are you sure you want to assign these :number entries?";
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
                            text: "Assign",
                            value: true,
                            visible: true,
                            className: "bg-primary",
                        }
                    },
                }).then((value) => {
                    if (value) {
                        var ajax_calls = [];
                        var assign_route = "{{ url($crud->route) }}/bulk-assign";



                        // submit an AJAX delete call
                        $.ajax({
                            url: assign_route,
                            type: 'POST',
                            data: { entries: crud.checkedItems, topics: topics},
                            success: function(result) {
                                // Show an alert with the result
                                new Noty({
                                    type: "success",
                                    text: "<strong>Entries assigned</strong><br>"+crud.checkedItems.length+" new entries have been added."
                                }).show();

                                crud.checkedItems = [];
                                crud.table.ajax.reload();
                            },
                            error: function(result) {
                                // Show an alert with the result
                                new Noty({
                                    type: "danger",
                                    text: "<strong>Assigning failed</strong><br>One or more entries could not be created. Please try again."
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
            background: #c8ced3;
        }
    </style>
@endpush
