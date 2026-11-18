{{-- APPROVE / REJECT buttons, shared by the place and change review pages.
     Needs: $approveUrl, $rejectUrl, $what (e.g. "this place" or "these changes").
     Rejecting needs a reason, which the owner will see. --}}
<section class="card decision" aria-labelledby="decision-heading">
    <h2 id="decision-heading">Your decision</h2>

    <form method="POST" action="{{ $approveUrl }}">
        @csrf
        <button type="submit" class="btn btn-block">
            <x-icon name="check" size="18" /> Approve {{ $what }}
        </button>
    </form>

    <form method="POST" action="{{ $rejectUrl }}" class="decision-reject" novalidate>
        @csrf
        <x-input name="reason" label="Reason for rejecting" type="textarea" rows="3" maxlength="1000"
                 :value="old('reason')" hint="The owner sees this, so say what to fix." />
        <button type="submit" class="btn btn-outline btn-danger btn-block">
            Reject {{ $what }}
        </button>
    </form>
</section>
