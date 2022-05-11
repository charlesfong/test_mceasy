@extends('template_admin.template')

@section('Content')
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Profile') }}
    </h2>
  </x-slot>

  <div>
      <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
          @if (Laravel\Fortify\Features::canUpdateProfileInformation())
              @livewire('profile.update-profile-information-form')

              <x-jet-section-border />
          @endif

          @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
              <div class="mt-10 sm:mt-0">
                  @livewire('profile.update-password-form')
              </div>

              <x-jet-section-border />
          @endif

          @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
              <div class="mt-10 sm:mt-0">
                  @livewire('profile.two-factor-authentication-form')
              </div>

              <x-jet-section-border />
          @endif

          <div class="mt-10 sm:mt-0">
              @livewire('profile.logout-other-browser-sessions-form')
          </div>

          @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
              <x-jet-section-border />

              <div class="mt-10 sm:mt-0">
                  @livewire('profile.delete-user-form')
              </div>
          @endif
      </div>
  </div>
</x-app-layout>

{{-- <div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-primary">
        <h4 class="card-title ">List Events</h4>
        <p class="card-category"> Events Collections</p>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead class=" text-primary">
              <th>
                No
              </th>
              <th>
                Name
              </th>
              <th>
                Dateline
              </th>
              <th>
                Created By
              </th>
              <th class='text-center'>
                Details
              </th>
            </thead>
            <tbody>
              @foreach ($events as $key => $value)
                  <td>{{$key+1}}</td>
                  <td>{{$value->name}}</td>
                  <td>{{date('d-M-Y', strtotime($value->dateline))}}</td>
                  <td>{{$value->created_by}}</td>
                  <td class='text-center'>
                      <button class="btn btn-primary btn-fab btn-fab-mini btn-round" onclick="open_detail('{{$value->id}}')">
                        <i class="material-icons">view_list</i>
                      </button>
                  </td>
              @endforeach

              
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div> --}}

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id='modal_details'>
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table">
          <thead>
              <tr>
                  <th class="text-center">#</th>
                  <th>Name</th>
                  <th>Job Position</th>
                  <th>Since</th>
                  <th class="text-right">Salary</th>
                  <th class="text-right">Actions</th>
              </tr>
          </thead>
          <tbody>
              <tr>
                  <td class="text-center">1</td>
                  <td>Andrew Mike</td>
                  <td>Develop</td>
                  <td>2013</td>
                  <td class="text-right">&euro; 99,225</td>
                  <td class="td-actions text-right">
                      <button type="button" rel="tooltip" class="btn btn-info">
                          <i class="material-icons">person</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-success">
                          <i class="material-icons">edit</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-danger">
                          <i class="material-icons">close</i>
                      </button>
                  </td>
              </tr>
              <tr>
                  <td class="text-center">2</td>
                  <td>John Doe</td>
                  <td>Design</td>
                  <td>2012</td>
                  <td class="text-right">&euro; 89,241</td>
                  <td class="td-actions text-right">
                      <button type="button" rel="tooltip" class="btn btn-info btn-round">
                          <i class="material-icons">person</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-success btn-round">
                          <i class="material-icons">edit</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-danger btn-round">
                          <i class="material-icons">close</i>
                      </button>
                  </td>
              </tr>
              <tr>
                  <td class="text-center">3</td>
                  <td>Alex Mike</td>
                  <td>Design</td>
                  <td>2010</td>
                  <td class="text-right">&euro; 92,144</td>
                  <td class="td-actions text-right">
                      <button type="button" rel="tooltip" class="btn btn-info btn-simple">
                          <i class="material-icons">person</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-success btn-simple">
                          <i class="material-icons">edit</i>
                      </button>
                      <button type="button" rel="tooltip" class="btn btn-danger btn-simple">
                          <i class="material-icons">close</i>
                      </button>
                  </td>
              </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary">Save changes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      
    </div>
  </div>
</div>
@endsection

<script>
  function open_detail(id){
    $("#modal_details").modal('show');
  }
</script>