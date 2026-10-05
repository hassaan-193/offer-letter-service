{{-- Shared form partial used by both create and edit views matching modern_portal theme --}}

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom: 20px;">
    <strong>Please correct the following errors:</strong>
    <ul style="margin: 6px 0 0 20px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Card 1: Personal Information --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--fts-maroon);"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Candidate Personal Details
        </h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label for="candidate_name">Candidate Full Name <span style="color:red;">*</span></label>
                <input type="text" id="candidate_name" name="candidate_name" class="form-control"
                    value="{{ old('candidate_name', $offer->candidate_name ?? '') }}"
                    placeholder="e.g. PRIYADARSAN PERINJANAM" required />
            </div>
            <div class="form-group">
                <label for="passport_number">Passport Number <span style="color:red;">*</span></label>
                <input type="text" id="passport_number" name="passport_number" class="form-control"
                    value="{{ old('passport_number', $offer->passport_number ?? '') }}"
                    placeholder="e.g. C1936146" required />
            </div>
            <div class="form-group">
                <label for="nationality">Nationality <span style="color:red;">*</span></label>
                <input type="text" id="nationality" name="nationality" class="form-control"
                    value="{{ old('nationality', $offer->nationality ?? '') }}"
                    placeholder="e.g. India" required />
            </div>
        </div>
    </div>
</div>

{{-- Card 2: Employment & Posting Details --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--fts-maroon);"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
            Employment Terms &amp; Posting
        </h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label for="designation">Designation / Role <span style="color:red;">*</span></label>
                <input type="text" id="designation" name="designation" class="form-control"
                    value="{{ old('designation', $offer->designation ?? '') }}"
                    placeholder="e.g. Mechanical Technician" required />
            </div>
            <div class="form-group">
                <label for="place_of_posting">Place of Posting <span style="color:red;">*</span></label>
                <input type="text" id="place_of_posting" name="place_of_posting" class="form-control"
                    value="{{ old('place_of_posting', $offer->place_of_posting ?? 'ABU DHABI') }}"
                    placeholder="e.g. ABU DHABI" required />
            </div>
            <div class="form-group">
                <label for="offer_date">Offer Letter Date <span style="color:red;">*</span></label>
                <input type="date" id="offer_date" name="offer_date" class="form-control"
                    value="{{ old('offer_date', isset($offer) ? $offer->offer_date?->format('Y-m-d') : date('Y-m-d')) }}"
                    required />
            </div>
            <div class="form-group">
                <label for="validity_date">Offer Validity Date <span style="color:red;">*</span></label>
                <input type="date" id="validity_date" name="validity_date" class="form-control"
                    value="{{ old('validity_date', isset($offer) ? $offer->validity_date?->format('Y-m-d') : date('Y-m-d', strtotime('+14 days'))) }}"
                    required />
            </div>
        </div>

        <div class="form-row" style="margin-top: 10px;">
            <div class="form-group">
                <label for="joining_date">Joining Date / Instruction</label>
                <input type="date" id="joining_date" name="joining_date" class="form-control"
                    value="{{ old('joining_date', isset($offer) ? $offer->joining_date?->format('Y-m-d') : '') }}" />
                <small style="color: var(--fts-text-muted);">Leave empty to use "once the work permit is issued"</small>
            </div>
            <div class="form-group">
                <label for="probation_period">Probation Period</label>
                <input type="text" id="probation_period" name="probation_period" class="form-control"
                    value="{{ old('probation_period', $offer->probation_period ?? '6 months') }}" />
            </div>
            <div class="form-group">
                <label for="contract_duration">Contract Duration</label>
                <input type="text" id="contract_duration" name="contract_duration" class="form-control"
                    value="{{ old('contract_duration', $offer->contract_duration ?? '2 years') }}" />
            </div>
        </div>

        <div class="form-row" style="margin-top: 10px;">
            <div class="form-group">
                <label for="working_hours">Working Hours</label>
                <input type="text" id="working_hours" name="working_hours" class="form-control"
                    value="{{ old('working_hours', $offer->working_hours ?? '8:00 AM to 5:00 PM including 1hour break (Differs accordingly)') }}" />
            </div>
            <div class="form-group">
                <label for="weekly_day_off">Weekly Day Off</label>
                <input type="text" id="weekly_day_off" name="weekly_day_off" class="form-control"
                    value="{{ old('weekly_day_off', $offer->weekly_day_off ?? 'Sunday') }}" />
            </div>
        </div>
    </div>
</div>

{{-- Card 3: Salary & Compensation --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--fts-maroon);"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            Compensation &amp; Allowances (Package Breakdown)
        </h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label for="salary_currency">Currency</label>
                <select id="salary_currency" name="salary_currency" class="form-control">
                    <option value="AED" {{ old('salary_currency', $offer->salary_currency ?? 'AED') === 'AED' ? 'selected' : '' }}>AED — UAE Dirham</option>
                    <option value="USD" {{ old('salary_currency', $offer->salary_currency ?? '') === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="SAR" {{ old('salary_currency', $offer->salary_currency ?? '') === 'SAR' ? 'selected' : '' }}>SAR</option>
                </select>
            </div>
        </div>

        <div class="form-row" style="margin-top: 10px;">
            <div class="form-group">
                <label for="basic_salary">Basic Salary (AED) <span style="color:red;">*</span></label>
                <input type="number" id="basic_salary" name="basic_salary" step="0.01" min="0" class="form-control"
                    value="{{ old('basic_salary', $offer->basic_salary ?? '') }}"
                    placeholder="e.g. 550" required oninput="computeTotal()" />
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label for="basic_salary_words">Basic Salary In Words <span style="color:red;">*</span></label>
                <input type="text" id="basic_salary_words" name="basic_salary_words" class="form-control"
                    value="{{ old('basic_salary_words', $offer->basic_salary_words ?? '') }}"
                    placeholder="e.g. FIVE HUNDRED FIFTY ONLY" required />
            </div>
        </div>

        <div class="form-row" style="margin-top: 10px;">
            <div class="form-group">
                <label for="other_allowances">Other Allowances (AED) <span style="color:red;">*</span></label>
                <input type="number" id="other_allowances" name="other_allowances" step="0.01" min="0" class="form-control"
                    value="{{ old('other_allowances', $offer->other_allowances ?? '') }}"
                    placeholder="e.g. 2450" required oninput="computeTotal()" />
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label for="other_allowances_words">Other Allowances In Words <span style="color:red;">*</span></label>
                <input type="text" id="other_allowances_words" name="other_allowances_words" class="form-control"
                    value="{{ old('other_allowances_words', $offer->other_allowances_words ?? '') }}"
                    placeholder="e.g. TWO THOUSAND FOUR HUNDRED FIFTY ONLY" required />
            </div>
        </div>

        <div class="form-row" style="margin-top: 10px; background: #fff5f8; padding: 15px; border-radius: 4px; border: 1px dashed var(--fts-maroon);">
            <div class="form-group">
                <label for="total_salary" style="color: var(--fts-maroon); font-weight: 700;">Total Package (AED) <span style="color:red;">*</span></label>
                <input type="number" id="total_salary" name="total_salary" step="0.01" min="0" class="form-control"
                    value="{{ old('total_salary', $offer->total_salary ?? '') }}"
                    placeholder="Auto-calculated" required style="font-weight: 700; font-size: 15px;" />
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label for="total_salary_words" style="color: var(--fts-maroon); font-weight: 700;">Total Package In Words <span style="color:red;">*</span></label>
                <input type="text" id="total_salary_words" name="total_salary_words" class="form-control"
                    value="{{ old('total_salary_words', $offer->total_salary_words ?? '') }}"
                    placeholder="e.g. THREE THOUSAND ONLY" required style="font-weight: 600;" />
            </div>
        </div>
    </div>
</div>

{{-- Card 4: Additional Terms & Benefits --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--fts-maroon);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Statutory Benefits &amp; Policies
        </h3>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label for="annual_leave">Annual Leave</label>
                <input type="text" id="annual_leave" name="annual_leave" class="form-control"
                    value="{{ old('annual_leave', $offer->annual_leave ?? '30 days paid annual leave') }}" />
            </div>
            <div class="form-group">
                <label for="air_ticket_allowance">Air Ticket Allowance</label>
                <input type="text" id="air_ticket_allowance" name="air_ticket_allowance" class="form-control"
                    value="{{ old('air_ticket_allowance', $offer->air_ticket_allowance ?? 'Once every two years, up to a maximum of AED 1,500') }}" />
            </div>
            <div class="form-group">
                <label for="notice_period">Cancellation / Notice Period</label>
                <input type="text" id="notice_period" name="notice_period" class="form-control"
                    value="{{ old('notice_period', $offer->notice_period ?? 'two months’ notice prior from leaving the company') }}" />
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label for="overtime_info">Overtime Policy Note</label>
            <textarea id="overtime_info" name="overtime_info" class="form-control" rows="2" placeholder="08 Working Hours can be changed as per the site conditions & Extra Hours will be paid as Over Time.">{{ old('overtime_info', $offer->overtime_info ?? '08 Working Hours can be changed as per the site conditions & Extra Hours will be paid as Over Time.') }}</textarea>
        </div>
    </div>
</div>

{{-- Card 5: Custom Section / Additional Terms & Special Conditions --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--fts-maroon);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Custom Section / Additional Terms &amp; Conditions (Optional)
        </h3>
    </div>
    <div class="card-body">
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="additional_terms_title">Custom Section Heading / Title</label>
            <input type="text" id="additional_terms_title" name="additional_terms_title" class="form-control"
                value="{{ old('additional_terms_title', $offer->additional_terms_title ?? '') }}"
                placeholder="e.g. 10. Special Site Allowance &amp; Terms / مخصصات خاصة بالموقع" />
            <small style="color: var(--fts-text-muted);">
                Leave empty to use the default title: "10. Additional Terms &amp; Special Conditions / شروط وأحكام إضافية"
            </small>
        </div>

        <div class="form-group">
            <label for="additional_terms">Custom Section Content / Details</label>
            <textarea id="additional_terms" name="additional_terms" class="form-control" rows="4" 
                placeholder="Enter the detailed clause text, project-specific terms, site allowances, or special conditions here...">{{ old('additional_terms', $offer->additional_terms ?? '') }}</textarea>
            <small style="color: var(--fts-text-muted);">
                Any content entered here will appear in the official Offer Letter (Web Preview, Candidate View, and Generated PDF) directly under the section heading above.
            </small>
        </div>
    </div>
</div>

@push('scripts')
<script>
function computeTotal() {
    const basic = parseFloat(document.getElementById('basic_salary').value) || 0;
    const allowances = parseFloat(document.getElementById('other_allowances').value) || 0;
    document.getElementById('total_salary').value = (basic + allowances).toFixed(2);
}
</script>
@endpush
