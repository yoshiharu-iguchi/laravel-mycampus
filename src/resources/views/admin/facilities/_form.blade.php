<div class="mb-3">
  <label class="form-label">名称 <span class="text-danger">*</span></label>
  <input type="text" name="name" class="form-control"
         value="{{ old('name', $facility->name) }}" required maxlength="255">
</div>

<div class="mb-3">
  <label class="form-label">住所</label>
  <input type="text" name="address" class="form-control"
         value="{{ old('address', $facility->address) }}" maxlength="255">
</div>

<div class="mb-3">
  <label class="form-label">最寄駅</label>
  <input type="text" name="nearest_station" class="form-control"
         value="{{ old('nearest_station', $facility->nearest_station) }}" maxlength="255">
</div>