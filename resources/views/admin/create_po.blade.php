@extends('template_admin.template')

@section('Content')
<div class="col-md-12">
    <div class="card ">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">Orders</h4>
        <p class="card-category">Create Pre Order</p>
      </div>
      <div class="card-body ">
        <form method="get" action="/" class="form-horizontal">
          <div class="row">
            <label class="col-sm-2 col-form-label label-checkbox">Custom Checkboxes &amp; radios</label>
            <div class="col-sm-4 col-sm-offset-1 checkbox-radios">
              <div class="form-check">
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" value="" checked=""> Checked
                  <span class="form-check-sign">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check">
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" value=""> Unchecked
                  <span class="form-check-sign">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check disabled">
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" value="" disabled="" checked=""> Disabled Checked
                  <span class="form-check-sign">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check disabled">
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" value="" disabled=""> Disabled Unchecked
                  <span class="form-check-sign">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
            </div>
            <div class="col-sm-5 checkbox-radios">
              <div class="form-check">
                <label class="form-check-label">
                  <input class="form-check-input" type="radio" name="exampleRadios" value="option1" checked=""> Radio is on
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check">
                <label class="form-check-label">
                  <input class="form-check-input" type="radio" name="exampleRadios" value="option2"> Radio is off
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check disabled">
                <label class="form-check-label">
                  <input class="form-check-input" type="radio" name="exampleRadios2" value="option1" checked="" disabled=""> Disabled Radio is on
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
              <div class="form-check disabled">
                <label class="form-check-label">
                  <input class="form-check-input" type="radio" name="exampleRadios2" value="option2" disabled=""> Disabled Radio is off
                  <span class="circle">
                    <span class="check"></span>
                  </span>
                </label>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-sm-2 col-form-label">Input with success</label>
            <div class="col-sm-10">
              <div class="form-group has-success bmd-form-group">
                <label for="exampleInput2" class="bmd-label-floating">Success input</label>
                <input type="text" class="form-control" id="exampleInput2">
                <span class="form-control-feedback">
                  <i class="material-icons">done</i>
                </span>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-sm-2 col-form-label">Input with error</label>
            <div class="col-sm-10">
              <div class="form-group has-danger bmd-form-group">
                <label for="exampleInput3" class="bmd-label-floating">Error input</label>
                <input type="email" class="form-control" id="exampleInput3">
                <span class="form-control-feedback">
                  <i class="material-icons">clear</i>
                </span>
              </div>
            </div>
          </div>
          <div class="row">
            <label class="col-sm-2 col-form-label">Column sizing</label>
            <div class="col-sm-10">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group bmd-form-group">
                    <input type="text" class="form-control" placeholder=".col-md-3">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group bmd-form-group">
                    <input type="text" class="form-control" placeholder=".col-md-4">
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group bmd-form-group">
                    <input type="text" class="form-control" placeholder=".col-md-5">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id='modal_details'>
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Contact Person</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
              <tr>
                  <th class="text-left">Name</th>
                  <th class="text-left">Phone 1</th>
                  <th class="text-left">Phone 2</th>
                  <th class="text-left">Email</th>
              </tr>
          </thead>
          <tbody id='modal_details_body'>
              {{-- <tr>
                  <td class="text-left">Amin</td>
                  <td class="text-left">085236057632</td>
                  <td class="text-left"></td>
                  <td class="td-actions text-right">
                      <button type="button" rel="tooltip" class="btn btn-fab btn-info">
                          <i class="material-icons">person</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-fab btn-success">
                          <i class="material-icons">edit</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-fab btn-danger">
                          <i class="material-icons">close</i>
                      </button>
                  </td>
              </tr> --}}
          </tbody>
        </table>
      </div>
      
    </div>
  </div>
</div>
@endsection

<script src="{{ asset('/template_admin/js/core/jquery.min.js') }}"></script>
<script>
    $(document).ready( function () {
        $("#list_customers").DataTable({
            searching: false,bLengthChange: false,rowReorder: true
        });
    });
    // function open_detail(id){
    //     $("#modal_details").modal('show');
    // }
    function open_detail(id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: "{{route('show_contact_person')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                'id': id
            },
            success: function (data) {
                var success = data['result'];
                var data    = data['data'];
                var html_   = "";
                for (var x = 0; x < data.length; x++) {
                    var tooltips = "<td class='td-actions text-right'>"+
                      "<button type='button' rel='tooltip' class='btn btn-fab btn-info'>"+
                          "<i class='material-icons'>person</i>"+
                      "</button>"+
                      "<button type='button' rel='tooltip' class='btn btn-fab btn-success'>"+
                          "<i class='material-icons'>edit</i>"+
                      "</button>"+
                      "<button type='button' rel='tooltip' class='btn btn-fab btn-danger'>"+
                          "<i class='material-icons'>close</i>"+
                      "</button>"+
                    "</td>";
                    html_ += "<tr><td class='text-left'>"+data[x]['name']+"</td><td class='text-left'>"+data[x]['phone_1']+"</td><td class='text-left'>"+data[x]['phone_2']+"</td><td class='text-left'>"+data[x]['email_']+"</td>"+tooltips+"</tr>";
                    console.log(data[x]);
                }
                $("#modal_details_body").html(html_);
                // var emailPembeli = data['email'];
                // var telpPembeli = data['phone'];
                // var namaPembeli = data['name'];
                // $("#edit-id").val(id);
                // $("#edit-name").val(namaPembeli);
                // $("#edit-address").val(tujuanPengiriman);

                $("#modal_details").modal('show');
            },
        });
    }
</script>