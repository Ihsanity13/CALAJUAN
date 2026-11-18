{{-- SHARED FORM FIELDS, included by both create.blade.php and edit.blade.php.
     old('name', $category->name ?? '') means: if the form just failed validation,
     show what the user typed; otherwise show the saved value (edit),
     or an empty box (create, where $category doesn't exist).
     <x-input> draws the label, the box, and the error message under it. --}}
@csrf

<x-input name="name" label="Name" :value="old('name', $category->name ?? '')" required autofocus />

<x-input name="icon" label="Icon" optional :value="old('icon', $category->icon ?? '')" />

<div class="form-actions">
    <button type="submit" class="btn">
        <x-icon name="check" size="18" />
        Save category
    </button>
    <a class="btn btn-ghost" href="{{ route('admin.categories.index') }}">Cancel</a>
</div>
