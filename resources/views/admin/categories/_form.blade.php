{{-- SHARED FORM FIELDS, included by both create.blade.php and edit.blade.php.
     old('name', $category->name ?? '') means: if the form just failed validation,
     show what the user typed; otherwise show the saved value (edit),
     or an empty box (create, where $category doesn't exist). --}}
@csrf

<label for="name">Name</label>
<input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required autofocus>
@error('name')
    <div class="error">{{ $message }}</div>
@enderror

<label for="icon">Icon (optional)</label>
<input type="text" id="icon" name="icon" value="{{ old('icon', $category->icon ?? '') }}">
@error('icon')
    <div class="error">{{ $message }}</div>
@enderror

<button type="submit" class="btn">Save</button>
<a href="{{ route('admin.categories.index') }}">Cancel</a>