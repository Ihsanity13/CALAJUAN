<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Proof that a business owner account belongs to a real, registered business.
// See the owner_verifications migration for the rules.
class OwnerVerification extends Model
{
    // draft = signed up with the basics only; the permit details aren't sent yet
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    // The registrations a Philippine business can show
    public const REGISTRATION_TYPES = [
        'dti' => 'DTI Business Name Registration (sole proprietor)',
        'sec' => 'SEC Registration (corporation or partnership)',
        'cda' => 'CDA Registration (cooperative)',
        'mayors_permit' => "Mayor's / Business Permit (Calauan LGU)",
    ];

    public const APPLICANT_ROLES = [
        'owner' => 'Owner',
        'manager' => 'Manager',
        'representative' => 'Authorized representative',
    ];

    // The form fields (the document and the review columns are set separately)
    protected $fillable = [
        'business_name', 'category_id', 'business_address', 'business_phone',
        'applicant_role', 'registration_type', 'registration_number',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Type of business (Café, Restaurant, Resort...)
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Still waiting for the owner to send the permit details?
    // (also true when they never sent anything at all)
    public function canBeSent(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function registrationTypeLabel(): string
    {
        return self::REGISTRATION_TYPES[$this->registration_type] ?? (string) $this->registration_type;
    }

    public function applicantRoleLabel(): string
    {
        return self::APPLICANT_ROLES[$this->applicant_role] ?? (string) $this->applicant_role;
    }

    // Is the uploaded document a PDF? (images are shown inline, PDFs as a link)
    public function documentIsPdf(): bool
    {
        return $this->document_path !== null && str_ends_with(strtolower($this->document_path), '.pdf');
    }
}
