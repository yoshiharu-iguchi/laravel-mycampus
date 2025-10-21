<div class="card-body">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">施設名 <span class="text-danger">*</span></label>
      <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', $facility->name) }}" maxlength="255" required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
      <label class="form-label">最寄駅</label>
      <input type="text" name="nearest_station" class="form-control @error('nearest_station') is-invalid @enderror"
             value="{{ old('nearest_station', $facility->nearest_station) }}" maxlength="255">
      @error('nearest_station')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
      <label class="form-label">住所</label>
      <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
             value="{{ old('address', $facility->address) }}" maxlength="255" placeholder="例：〇〇県〇〇市…">
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>