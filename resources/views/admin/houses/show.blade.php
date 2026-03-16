<div class="card-glass">
  <div class="row">
    <div class="col-md-12">
      <!-- <h4 class="text-white mb-4">
        <i data-feather="home" class="me-2"></i>House Information
      </h4> -->
      
      <table class="table table-dark">
        <tbody>
          <tr>
            <th style="width: 30%;">ID</th>
            <td>{{ $house->id }}</td>
          </tr>
          <tr>
            <th class="px-4 py-2 text-left font-semibold text-light" style="width: 30%;">House Number</th>
            <td class="px-4 py-2 whitespace-nowrap">{{ $house->house_no }}</td>
          </tr>
          <tr>
            <th>Name</th>
            <td>{{ $house->name ?: 'N/A' }}</td>
          </tr>
          <tr>
            <th>Username</th>
            <td>{{ $house->username }}</td>
          </tr>
          <tr>
            <th>Type</th>
            <td>{{ $house->type ?: 'N/A' }}</td>
          </tr>
          <tr>
            <th>Password</th>
            <td>••••••••</td>
          </tr>
          <tr>
            <th>GE Group</th>
            <td>{{ $house->city ? $house->city->name : 'N/A' }}</td>
          </tr>
          <tr>
            <th>GE Node</th>
            <td>{{ $house->sector ? $house->sector->name : 'N/A' }}</td>
          </tr>
          <tr>
            <th>Address</th>
            <td>{{ $house->address ?: 'N/A' }}</td>
          </tr>
          <tr>
            <th>Status</th>
            <td>
              <span class="badge {{ $house->status === 1 ? 'bg-success' : 'bg-danger' }}">
                {{ ($house->status ? 'Active' : 'Inactive') }}
              </span>
            </td>
          </tr>
          <tr>
            <th>Created At</th>
            <td>{{ $house->created_at ? $house->created_at->format('M d, Y H:i A') : 'N/A' }}</td>
          </tr>
          <tr>
            <th>Updated At</th>
            <td>{{ $house->updated_at ? $house->updated_at->format('M d, Y H:i A') : 'N/A' }}</td>
          </tr>
        </tbody>
      </table>
      
    </div>
  </div>
</div>

<script>
  feather.replace();
</script>
