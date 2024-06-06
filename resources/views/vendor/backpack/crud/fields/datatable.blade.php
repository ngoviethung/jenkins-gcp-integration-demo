<!-- Datatable -->
@php

@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.wrapper_end')
<table class="table table-bordered" id="content-table">
    <thead>
        <tr>
            <th class="select-checkbox"></th>
            <th>Id</th>
            <th>Name</th>
            <th>Image</th>
        </tr>
    </thead>
</table>

@if ($crud->checkIfFieldIsFirstOfItsType($field))

    @push('crud_fields_styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css"/>
    @endpush

    @push('crud_fields_scripts')
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {

                @if($crud->entry)
                    @php
                        $itemIds = $crud->entry->items()->pluck('id')->toArray();
                        $itemIds = implode(',', $itemIds);
                    @endphp
                @endif
                let selectedIds = [{{ isset($itemIds) && $itemIds ? $itemIds : ''}}];
                let table = $('#content-table').DataTable({
                    processing: true,
                    serverSide: true,
                    buttons: [
                        'selectAll',
                        'selectNone'
                    ],dom: 'Blfrtip',
                    aLengthMenu: [
                        [25, 50, 100, 200, -1],
                        [25, 50, 100, 200, "All"]
                    ],
                    ajax: {
                        url: '{!! url('api-admin/items/list_table') !!}',
                        data: function ( d ) {
                            d.selectedIds = selectedIds.join(',');
                            // d.custom = $('#myInput').val();
                            // etc
                        }
                    },
                    columnDefs: [ {
                        orderable: true,
                        className: 'select-checkbox',
                        targets:   0
                    } ],
                    drawCallback: function() {
                        $(this).find('tr').each(function (i, e) {
                            let id = $(e).find('.row-id').first().data('id')
                            if(selectedIds.indexOf(id) > -1) {
                                $(e).addClass('selected');
                            }
                        })
                    },
                    columns: [
                        {
                            data:   "active",
                            render: function ( data, type, row ) {
                                if ( type === 'display' ) {
                                    return '<span class="row-id" data-id="'+ row.id +'"></span>';
                                }
                                return data;
                            },
                            className: "dt-body-center"
                        },
                        { data: 'id', name: 'id' },
                        { data: 'name', name: 'name' },
                        {
                            data: 'image',
                            render: function (data, type, row) {
                                return '<img src="' + data +'" class="editor-active" width="100px"/>';
                            }
                        }
                    ],
                });

                $('#content-table tbody').on( 'click', 'tr', function () {
                    $(this).toggleClass('selected');
                    let id = $(this).find('.row-id').first().data('id');
                    if($(this).hasClass('selected')) {
                        selectedIds.push(id);
                    } else {
                        removeElement(selectedIds, id)
                    }
                    console.log(selectedIds)
                });

                function removeElement(array, elem) {
                    var index = array.indexOf(elem);
                    if (index > -1) {
                        array.splice(index, 1);
                    }
                }

                $("form").submit(function() {
                    $('select[name="content-table_length"]').remove();

                    if(selectedIds.length) {
                        $("<input />").attr("type", "hidden")
                            .attr("name", "selectedIds")
                            .attr("value", selectedIds.join(','))
                            .appendTo("form");
                    }
                });
            })
        </script>
    @endpush

@endif
