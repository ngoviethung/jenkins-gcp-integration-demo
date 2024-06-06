<a style="margin: 0 20px;" href="#" class="btn btn-primary" id="export_to_firebase_button">
    <i class="fa fa-export"></i>
    Export To Firebase
</a>
<img id="loading-image" style="display: none" src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/fancybox_loading.gif" width="30">


<!-- Modal -->
<div class="modal fade" id="submitJsonForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="export_form" method="post" action="{{ url('admin/submitToFirebase') }}">
                    <textarea name="changeLogs" id="json_input" style="width: 100%">
                    </textarea>
                    <input type="hidden" name="from" value="topic">
                    @csrf
                    <button type="submit">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@push('after_scripts')
    <style>
        .modal-backdrop {
            background: none;
            z-index: -1;
        }
    </style>
    <script
        src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"
        integrity="sha256-xH4q8N0pEzrZMaRmd7gQVcTZiFei+HfRTBPJ1OGXC0k="
        crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#export_to_firebase_button').on('click', function () {
                if(confirm('You want to submit new version of Remote Config ?')) {
                    $('#loading-image').show();

                    $.ajax({
                        url: "{{ url('admin/export_to_firebase') }}",
                        method: "POST",
                        data: {

                            from: 'topic'
                        },
                        success: function (data) {
                            document.getElementById('json_input').value = JSON.stringify(data, undefined, 0);
                            $('#loading-image').hide();
                            $('#submitJsonForm').modal('show')
                        }
                    })
                }
            })
        })
    </script>
@endpush
