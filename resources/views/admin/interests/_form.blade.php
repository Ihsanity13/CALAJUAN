{{-- SHARED FORM FIELDS for create and edit (same pattern as the category form). --}}
@csrf

<x-input name="name" label="Name" :value="old('name', $interest->name ?? '')" required autofocus maxlength="50"
         hint="What a place is good for, e.g. Nature, Food, Adventure, Relaxation." />

<div class="form-actions">
    <button type="submit" class="btn">
        <x-icon name="check" size="18" />
        Save interest
    </button>
    <a class="btn btn-ghost" href="{{ route('admin.interests.index') }}">Cancel</a>
</div>
