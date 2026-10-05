@extends('layouts.admin')
@section('title', 'Offer Letter Preview — ' . $offer->candidate_name)

@push('styles')
<style>
/* Document preview styling */
.preview-desk {
    background-color: #525659;
    padding: 30px 15px;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 30px;
}

.offer-page-sheet {
    width: 816px; /* standard Letter width (8.5in * 96dpi) */
    min-height: 1056px; /* standard Letter height (11in * 96dpi) */
    background-color: #ffffff;
    background-image: url('{{ asset("images/fts_letterhead.jpg") }}');
    background-repeat: no-repeat;
    background-position: top center;
    background-size: 816px 1056px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    position: relative;
    padding: 195px 70px 45px 70px; /* 195px leaves complete clearance below letterhead header */
    font-family: Arial, 'DejaVu Sans', sans-serif;
    font-size: 11.5px;
    line-height: 1.38;
    color: #000000;
    box-sizing: border-box;
}

.ar-text {
    direction: rtl;
    text-align: right;
    font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, sans-serif;
    margin-top: 4px;
}

@media print {
    body { background: white !important; }
    .main-header, .content-header, .main-footer, .no-print { display: none !important; }
    .content-wrapper { padding: 0 !important; max-width: 100% !important; margin: 0 !important; }
    .preview-desk { background: transparent !important; padding: 0 !important; gap: 0 !important; }
    .offer-page-sheet {
        box-shadow: none !important;
        margin: 0 !important;
        page-break-after: always;
        width: 100% !important;
        height: 100vh !important;
    }
}
</style>
@endpush

@section('content')
<div class="content-header no-print">
    <div>
        <h1 class="page-title">Offer Letter Preview</h1>
        <p class="page-subtitle">Candidate: <strong>{{ $offer->candidate_name }}</strong> &bull; Status: <span class="badge {{ $offer->status_badge['class'] }}">{{ $offer->status_badge['label'] }}</span></p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <ol class="breadcrumb" style="margin: 0;">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.offers.index') }}">Offer Letters</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.offers.show', $offer) }}">{{ $offer->candidate_name }}</a></li>
            <li class="breadcrumb-item active" style="color: var(--fts-maroon); font-weight: 600;">Preview</li>
        </ol>

        <div style="display: flex; gap: 8px; margin-left: 15px;">
            <a href="{{ route('admin.offers.show', $offer) }}" class="btn btn-secondary">
                &larr; Back to Details
            </a>
            @if($offer->status !== 'signed')
                <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-ghost" style="border-color: #ced4da;">
                    Edit Offer
                </a>
            @endif
            @if($offer->status === 'draft')
                <form action="{{ route('admin.offers.publish', $offer) }}" method="POST" style="display:inline;" onsubmit="return confirm('Publish this offer and generate candidate signing link?');">
                    @csrf
                    <button type="submit" class="btn btn-primary">Publish Offer</button>
                </form>
            @endif
            <button type="button" class="btn btn-ghost" onclick="window.print();" style="border-color: #ced4da;">
                Print
            </button>
            <a href="{{ route('admin.offers.download.original', $offer) }}" class="btn btn-primary">
                Download PDF
            </a>
        </div>
    </div>
</div>

<div class="preview-desk">

    {{-- PAGE 1 --}}
    <div class="offer-page-sheet" id="page-1">
        <div style="text-align: center; font-size: 10.5px; margin-bottom: 6px;">Rev-06-23</div>
        <div style="text-align: right; font-weight: bold; margin-bottom: 8px; font-size: 11.5px;">Date: {{ $offer->offer_date->format('d/m/Y') }}</div>
        
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px;">
            <div style="font-weight: bold;">To: Mr./Ms. {{ strtoupper($offer->candidate_name) }}</div>
            <div style="font-size: 11px;">Page 1 of 5</div>
        </div>
        <div style="margin-bottom: 3px;">Passport NO: {{ $offer->passport_number }}</div>
        <div style="margin-bottom: 14px;">Nationality: - {{ $offer->nationality }}</div>

        <div style="text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 4px; font-size: 13px;">
            Sub: Letter of Appointment
        </div>
        <div style="text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 14px; font-size: 11.5px;">
            Validity of this offer letter: {{ $offer->validity_date->format('d/m/Y') }}
        </div>

        <div style="margin-bottom: 8px;">
            We take pleasure to inform you that, management has decided to appoint you as a <strong>{{ $offer->designation }}</strong> on the following terms and conditions.
        </div>

        <div class="ar-text" style="font-weight: bold; text-decoration: underline;">خطاب التعيين</div>
        <div class="ar-text" style="margin-bottom: 14px;">
            يسرّنا إبلاغكم بأن الإدارة قد قررت تعيينكم في منصب {{ $offer->designation }} وفقًا للشروط والأحكام التالية:
        </div>

        <div style="font-weight: bold; margin-bottom: 2px;">01: Place of posting</div>
        <div style="margin-bottom: 4px;">
            Your posting will be at {{ strtoupper($offer->place_of_posting) }} for the present. However, during the employment with the company, you may be posted/transferred to any of the offices at different locations in United Arab Emirates.
        </div>
        <div class="ar-text" style="font-weight: bold;">01 / مكان العمل</div>
        <div class="ar-text" style="margin-bottom: 14px;">
            سيكون مقر عملك في {{ $offer->place_of_posting }} في الوقت الحالي. ومع ذلك، خلال فترة عملك لدى الشركة، يجوز تكليفك بالعمل أو نقلك إلى أي من مكاتب الشركة في مواقع مختلفة داخل دولة الإمارات العربية المتحدة.
        </div>

        <div style="font-weight: bold; margin-bottom: 2px;">02: Working hours</div>
        <div>{{ $offer->working_hours ?? '8:00 AM to 5:00 PM including 1hour break (Differs accordingly)' }}</div>
        <div>{{ $offer->weekly_day_off ? $offer->weekly_day_off . ' – Weekly Day Off' : 'Sunday – Weekly Day Off' }}</div>
        <div>{{ $offer->overtime_info ?? '08 Working Hours can be changed as per the site conditions & Extra Hours will be paid as Over Time.' }}</div>
    </div>

    {{-- PAGE 2 --}}
    <div class="offer-page-sheet" id="page-2">
        <div style="text-align: right; font-size: 11px; margin-bottom: 8px;">Page 2 of 5</div>

        <div class="ar-text" style="font-weight: bold;">02 / ساعات العمل</div>
        <div class="ar-text">
            قد تختلف ساعات العمل حسب ظروف العمل) من الساعة 8:00 صباحًا إلى الساعة 5:00 مساءً، بما في ذلك استراحة لمدة ساعة واحدة.
        </div>
        <div class="ar-text">يوم الأحد – يوم الراحة الأسبوعية</div>
        <div class="ar-text" style="margin-bottom: 14px;">
            يبلغ عدد ساعات العمل 8 ساعات يوميًا ، ويجوز تعديل ساعات العمل وفقًا لظروف ومتطلبات موقع العمل، كما سيتم احتساب ودفع أي ساعات عمل إضافية كـ ساعات عمل إضافية (Overtime).
        </div>

        <div style="font-weight: bold; margin-bottom: 4px;">03: Salary &amp; Allowance</div>
        <div>(3a) Basic Salary: {{ number_format($offer->basic_salary, 0) }} AED (Arab Emirates Dirham {{ strtoupper($offer->basic_salary_words) }})</div>
        <div>Other Allowances: {{ number_format($offer->other_allowances, 0) }} AED (Arab Emirates Dirham {{ strtoupper($offer->other_allowances_words) }})</div>
        <div><strong>Total Salary: {{ number_format($offer->total_salary, 0) }} AED (Arab Emirates Dirham {{ strtoupper($offer->total_salary_words) }})</strong></div>

        <div style="font-weight: bold; margin-top: 8px; margin-bottom: 2px;">(3b) Company Allowances.</div>
        <div>Company will provide you</div>
        <div style="margin-bottom: 10px;">
            1. 30 days paid annual leave. Air ticket allowance is provided once every two years, up to a maximum of AED 1,500, upon submission of a confirmed flight ticket copy to HR for reimbursement. You will be also entitled to other allowances/perquisites/facilities as per the labor law of United Arab Emirates &amp; the policy of the company as per Standard operation procedure of FTS.
        </div>

        <div class="ar-text" style="font-weight: bold;">(03) الراتب والبدلات</div>
        <div class="ar-text">(3:أ) الراتب الأساسي: {{ number_format($offer->basic_salary, 0) }} درهمًا إماراتيًا ({{ $offer->basic_salary_words_ar ?? 'خمسمائة وخمسون درهمًا إماراتيًا فقط لا غير' }}).</div>
        <div class="ar-text">البدلات الأخرى: {{ number_format($offer->other_allowances, 0) }} درهمًا إماراتيًا ({{ $offer->other_allowances_words_ar ?? 'ألفان وأربعمائة وخمسون درهمًا إماراتيًا فقط لا غير' }}).</div>
        <div class="ar-text">إجمالي الراتب: {{ number_format($offer->total_salary, 0) }} درهم إماراتي ({{ $offer->total_salary_words_ar ?? 'ثلاثة آلاف درهم إماراتي فقط لا غير' }}).</div>
        <div class="ar-text" style="margin-top: 4px; font-weight: bold;">(3:ب) بدلات الشركة:</div>
        <div class="ar-text" style="margin-bottom: 14px;">
            الإجازة السنوية: يحق لك الحصول على إجازة سنوية مدفوعة الأجر لمدة 30 يومًا , بدل تذكرة الطيران: يتم توفير بدل تذكرة سفر مره واحده كل سنتين بحد اقصي 1500 درهم اماراتي , وذلك عند تقديم نسخة من تذكرة الطيران المؤكده الي قسم الموارد البشريه لاسترداد قسمة التذكره. كما يحق لك الحصول على البدلات والمزايا والتسهيلات الأخرى وفقًا لقانون العمل في دولة الإمارات العربية المتحدة، وكذلك وفقًا لسياسات الشركة واجراءات التشغيل القياسية المعتمده لدي الشركة.
        </div>

        <div style="font-weight: bold; margin-bottom: 2px;">04: Increments</div>
        <div>
            Your increments and future prospects in the company shall entirely depend on your efficiency, hard work, sincerity, good conduct and such other relevant factors. Increment in no case shall be automatic and/ or a matter of right.
        </div>
    </div>

    {{-- PAGE 3 --}}
    <div class="offer-page-sheet" id="page-3">
        <div style="text-align: right; font-size: 11px; margin-bottom: 8px;">Page 3 of 5</div>

        <div class="ar-text" style="font-weight: bold;">04 / الزيادات</div>
        <div class="ar-text" style="margin-bottom: 14px;">
            تعتمد الزيادات والتوقعات المستقبلية في الشركة اعتمادًا كليًا على كفاءتك وعملك الجاد وإخلاصك وحسن سلوكك وغيرها من العوامل ذات الصلة. لا تكون الزيادة في أي حال تلقائية و / أو مسألة حق.
        </div>

        <div style="font-weight: bold; margin-bottom: 4px;">05: Medical fitness and verification of fitness</div>
        <div>Your appointment is subjected to:</div>
        <div style="padding-left: 15px;">
            <div>a. Medical fitness by approved medical officer specified by the Government of UAE.</div>
            <div>b. The management has the right to get you medically examined by any certified medical practitioner during the period of your service. In case you are found medically unfit to continue with the job, you will lose to lien on the job.</div>
        </div>

        <div class="ar-text" style="font-weight: bold; margin-top: 10px;">05 / اللياقة الطبية والتحقق من اللياقة البدنية</div>
        <div class="ar-text">يخضع تعيينك إلى:</div>
        <div class="ar-text">ا. اللياقة الطبية من قبل مسؤول طبي معتمد من قبل حكومة الإمارات العربية المتحدة.</div>
        <div class="ar-text" style="margin-bottom: 14px;">ب. الإدارة لها الحق في إجراء فحص طبي لك من قبل أي ممارس طبي معتمد خلال فترة خدمتك. في حال وجدت أنك غير لائق طبيا لمواصلة العمل فسوف تخسر مقابل الحصول على الوظيفة.</div>

        <div style="font-weight: bold; margin-bottom: 4px;">06. Termination of permanent service</div>
        <div style="margin-bottom: 4px;">6a) At present your will be in probation for {{ $offer->probation_period ?? '6 months' }} and the permanent contract will be for {{ $offer->contract_duration ?? '2 years' }}.</div>
        <div style="margin-bottom: 4px;">(6b) In cases of misconduct, disloyalty, Commission of an act involving moral turpitude will cause immediate action by company interest.</div>
        <div style="margin-bottom: 4px;">(6c)) In case the contract is broken for any reason from company side or labor side before finishing 1 year, he should pay emoluments of AED 5000/- (Five Thousand Arab Emirate Dirham) In return for all fees and expenses spent on preparing the worker for work and conforming to the conditions of civil defense such as examinations, training of the worker and arranging the official dress.</div>
        <div style="margin-bottom: 4px;">(6d) In case the contract is broken for any reason from company side or labor side before finishing the 2-yearcontract period, he should pay emoluments of AED 2500/- (Two Thousand Five Hundred Arab Emirate Dirham) In return for all fees and expenses spent on preparing the worker for work and conforming to the conditions of civil defense such as examinations, training of the worker and arranging the official dress.</div>
        <div>(6e) However if there is a discrepancy in the copies of documents or certificates given by you as a proof of above, we retain the right to review our offer of employment contract and the employment have to pay the fine AED 10,000 along with fines and legal expenses which are received from the authorities on company’s name because of the same.</div>
    </div>

    {{-- PAGE 4 --}}
    <div class="offer-page-sheet" id="page-4">
        <div style="text-align: right; font-size: 11px; margin-bottom: 8px;">Page 4 of 5</div>

        <div class="ar-text" style="font-weight: bold;">06 / إنهاء الخدمة الدائمة</div>
        <div class="ar-text">6 أ) في الوقت الحالي سوف تكون قيد الاختبار لمدة 6 أشهر وسيكون العقد الدائم لمدة عامين.</div>
        <div class="ar-text">(6 ب) في حالات سوء السلوك عدم الولاء فإن ارتكاب فعل ينطوي على ضرر أخلاقي يؤدي إلى اتخاذ إجراءات فورية من جانب مصلحة الشركة.</div>
        <div class="ar-text">(6 ج) في حالة رغبة الموظف في فسخ العقد لأي سبب من الأسباب أو رغبة الشركة بفسخ العقد بسبب تحصل العامل على ثلاثة انذارات خطية او اكثر قبل الانتهاء من عام واحد من مدة العقد ، يجب عليه دفع غرامة قدرها 5000 درهم - (خمسة آلاف درهم إماراتي) لقاء كل الاتعاب و النفقات التي انفقت على تجهيز العامل للعمل و مطابقة شروط الدفاع المدني كامتحان و تدريب العامل و تامين الباس الرسمي.</div>
        <div class="ar-text">(6 د) في حالة رغبة الموظف في فسخ العقد لأي سبب من الأسباب أو رغبة الشركة بفسخ العقد بسبب تحصل العامل على ثلاثة انذارات خطية او اكثر قبل الانتهاء من مدة العقد ، يجب عليه دفع غرامة بقيمة 2500 (درهم إماراتي - ألفان وخمسمائة درهم إماراتي) لقاء كل الاتعاب و النفقات التي انفقت على تجهيز العامل للعمل و مطابقة شروط الدفاع المدني كامتحان و تدريب العامل و تامين الباس الرسمي.</div>
        <div class="ar-text" style="margin-bottom: 14px;">(6 هـ) ومع ذلك ، إذا كان هناك تعارض في نسخ المستندات أو الشهادات التي قدمتها كدليل أعلاه ، فإننا نحتفظ بالحق في مراجعة عرضنا الخاص بعقد العمل وعلى العامل دفع غرامة قدرها 10،000 درهم.</div>

        <div style="font-weight: bold; margin-bottom: 4px;">07 General</div>
        <div style="margin-bottom: 4px;">(7a) The service rules and regulations including conduct, discipline and administrative orders will cover you and any such other rules or orders of the company that may be in force from time to time.</div>
        <div style="margin-bottom: 4px;">(7b) You will hand over the charge of letter of authority or power of attorney issued to you or any property/material if the company in your possession at the time of cessation of your employment within the company.</div>
        <div style="margin-bottom: 4px;">(7c) By signing this Contract/Offer Letter the employee is confirming the reading, understanding &amp; accepting of all the FIRE TECHNICAL SERVICES (FTS) Terms and Condition &amp; STANDARD OPERATION PROCEDURE (SOP) with all its revision till the date of this Contract/Offer Letter.</div>
        <div style="margin-bottom: 8px;">(7d) After completing or cancelling the Contract with FTS, the employee is not permitted work with FTS clients or sister concern companies without a written acceptance from FTS.</div>

        <div class="ar-text" style="font-weight: bold;">07 / عام</div>
        <div class="ar-text">(7 أ) ستغطي قواعد ولوائح الخدمة بما في ذلك السلوك والانضباط والأوامر الإدارية أي قواعد أو أوامر أخرى للشركة قد تكون سارية من وقت لآخر.</div>
        <div class="ar-text">(7 ب) سوف تقوم بتسليم أي خطاب تفويض أو توكيل صادر إليك أو أي ممتلكات / مواد إذا كانت بحوزتك من قبل الشركة وقت توقف عملك داخل الشركة.</div>
        <div class="ar-text">(7 ج) بتوقيع هذا العقد ، يجب على كل موظف اتباع أساس إجراءات التشغيل القياسية والملحق ذي الصلة من الشركة.</div>
        <div class="ar-text">(7 د) بعد الإنتهاء أو إلغاء العقد مع الشركة , لا يحق للموظف العمل مع عملاء الشركة أو الشركات الشقيقة المعنية دون موافقة خطية من الشركة.</div>
    </div>

    {{-- PAGE 5 --}}
    <div class="offer-page-sheet" id="page-5">
        <div style="text-align: right; font-size: 11px; margin-bottom: 8px;">Page 5 of 5</div>

        <div style="font-weight: bold; margin-bottom: 2px;">08 Joining date</div>
        <div>{{ $offer->joining_date ? 'You will report to duty on ' . $offer->joining_date->format('d/m/Y') : 'You will report to duty once the work permit is issued' }}</div>
        <div class="ar-text" style="font-weight: bold;">08 / تاريخ الانضمام</div>
        <div class="ar-text" style="margin-bottom: 12px;">بمجرد إصدار التأشيرة.</div>

        <div style="font-weight: bold; margin-bottom: 2px;">09 Cancellation of Contract</div>
        <div>In case the employee is willing to break the contract and leave the company, the employee should give a two months’ notice prior from leaving the company.</div>
        <div class="ar-text" style="margin-bottom: 12px;">في حالة استعداد الموظف لكسر العقد ومغادرة الشركة يجب على الموظف تقديم إشعار قبل شهرين من مغادرة الشركة.</div>

        @if(!empty($offer->additional_terms))
        <div style="font-weight: bold; margin-bottom: 2px;">{{ $offer->additional_terms_title ?: '10. Additional Terms & Special Conditions' }}</div>
        <div style="white-space: pre-line; line-height: 1.4; margin-bottom: 12px;">{!! nl2br(e($offer->additional_terms)) !!}</div>
        @endif

        <div style="text-align: center; margin-top: 12px;">We look forward a long, successful and pleasant association with us</div>
        <div class="ar-text" style="text-align: center; margin-bottom: 12px;">ونحن نتطلع إلى وجود علاقة طويلة وناجحة وممتعة معنا</div>

        <div class="ar-text" style="text-align: left; margin-bottom: 2px;">فاير للخدمات التقنية</div>
        <div style="font-weight: bold;">For Fire Technical Services</div>
        <div style="margin-top: 20px; margin-bottom: 5px; font-size: 11px;">(Signature of owner/Managing Director)</div>
        <hr style="border: none; border-top: 1px solid #333; margin: 10px 0 14px 0;">

        <div style="text-align: center; font-weight: bold; font-size: 13px; margin-bottom: 6px;">
            Acknowledgement and Acceptance
        </div>
        <div style="font-size: 11px; line-height: 1.4; margin-bottom: 4px;">
            I have read and understood the above terms and conditions and FTS SOP with all its revision and unconditionally accept the same.
        </div>
        <div class="ar-text" style="font-size: 11px; line-height: 1.4; margin-bottom: 12px;">
            لقد قرأت وفهمت جميع الشروط والأحكام المذكورة أعلاه، وكذلك إجراءات التشغيل القياسية (SOP) الخاصة بشركة FTS بجميع تعديلاتها وأوافق عليها دون قيد أو شرط
        </div>

        <div style="font-size: 12px; margin-bottom: 3px;"><strong>Name: Mr./Ms. {{ strtoupper($offer->candidate_name) }}</strong></div>
        <div style="font-size: 12px; margin-bottom: 14px;"><strong>Passport No: {{ $offer->passport_number }}</strong></div>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ccc;">
            <div style="min-width: 250px;">
                <strong>Signature:</strong>
                @if($offer->signature)
                    <div style="margin-top: 4px;">
                        <img src="{{ $offer->signature->signature_data }}" alt="Digital Signature" style="max-height: 50px; max-width: 200px; display: block;" />
                    </div>
                @else
                    <span style="color: #888; font-style: italic; margin-left: 8px;">[Pending Digital Signature]</span>
                @endif
            </div>
            <div>
                <strong>Finger Print:</strong>
                <span style="display: inline-block; width: 60px; height: 38px; border: 1px dashed #999; vertical-align: middle; margin-left: 8px;"></span>
            </div>
            <div>
                <strong>Date:</strong> <span>{{ $offer->signed_at ? $offer->signed_at->format('d/m/Y') : date('d/m/Y') }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
