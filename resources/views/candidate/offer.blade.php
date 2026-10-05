<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description" content="Offer / Appointment Letter — {{ $offer->candidate_name }}" />
    <title>Offer Letter — {{ $offer->candidate_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/candidate.css') }}" />
</head>
<body>

{{-- Header styled with modern_portal top navbar theme --}}
<header class="site-header no-print">
    <div class="site-header-inner">
        <div class="company-brand">
            <div class="company-logo">
                <img src="{{ asset('images/logo-top.png') }}" alt="FTS Logo" onerror="this.src='{{ asset('images/logo.png') }}'" />
            </div>
            <div class="company-title-wrap">
                <div class="company-name">Facilities &amp; Technical Services LLC</div>
                <div class="company-subtitle">Offer / Appointment Letter Portal</div>
            </div>
        </div>
        <div class="candidate-info-header">
            <div class="cand-text">
                <span class="cand-name">{{ $offer->candidate_name }}</span>
                <span class="cand-desig">{{ $offer->designation }}</span>
            </div>
            <span class="cand-badge {{ $offer->status === 'signed' ? 'signed' : '' }}">
                {{ $offer->status === 'signed' ? 'Signed' : 'Offer Letter' }}
            </span>
        </div>
    </div>
</header>

{{-- Reading progress bar --}}
<div class="progress-bar-wrap no-print">
    <div class="progress-bar" id="progress-bar"></div>
    <div class="progress-label-wrap" id="progress-label">0% Read</div>
</div>

@if($offer->status === 'signed')
<div class="signed-notice no-print">
    <div style="display: flex; align-items: center; gap: 10px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span>This offer letter was signed and accepted on <strong>{{ $offer->signed_at->format('d M Y, h:i A') }}</strong>.</span>
    </div>
    <a href="{{ route('candidate.offer.download', $offer->token) }}" class="btn-download-inline">
        Download Signed Copy
    </a>
</div>
@endif

{{-- 5-Page Letterhead Document Desk --}}
<main class="offer-viewer" id="offer-viewer">
    <div class="offer-document">

        {{-- ========================================================
             PAGE 1 OF 5
             ======================================================== --}}
        <div class="offer-page-sheet" id="page-1">
            <div style="text-align: center; font-size: 10.5px; margin-bottom: 6px;">Rev-06-23</div>
            <div style="text-align: right; font-weight: bold; margin-bottom: 8px; font-size: 11.5px;">Date: {{ $offer->offer_date->format('d/m/Y') }}</div>
            
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px;">
                <div style="font-weight: bold;">To: Mr./Ms. {{ strtoupper($offer->candidate_name) }}</div>
                <div class="page-num-top">Page 1 of 5</div>
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

        {{-- ========================================================
             PAGE 2 OF 5
             ======================================================== --}}
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

        {{-- ========================================================
             PAGE 3 OF 5
             ======================================================== --}}
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

        {{-- ========================================================
             PAGE 4 OF 5
             ======================================================== --}}
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

        {{-- ========================================================
             PAGE 5 OF 5 (Acceptance & Single Signature Block)
             ======================================================== --}}
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

            {{-- Signature line on Sheet 5 (The ONLY signature block on the letter) --}}
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ccc;">
                <div style="min-width: 250px;">
                    <strong>Signature:</strong>
                    @if($offer->signature)
                        <div style="margin-top: 4px;">
                            <img src="{{ $offer->signature->signature_data }}" alt="Digital Signature" style="max-height: 50px; max-width: 200px; display: block;" />
                        </div>
                    @else
                        <span id="pending-signature-placeholder" style="color: #888; font-style: italic; margin-left: 8px;">[Pending Digital Signature]</span>
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

            {{-- Candidate signing workflow controls --}}
            @if($offer->status !== 'signed')
                <div id="candidate-action-panel" class="no-print">
                    {{-- Reading reminder --}}
                    <div id="reading-reminder" class="reading-reminder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>Please review all 5 pages. Scroll to the bottom to unlock acknowledgment and digital signing.</span>
                    </div>

                    {{-- Acknowledgment checkbox --}}
                    <div class="ack-box">
                        <label class="ack-check-label disabled" id="ack-check-label">
                            <input type="checkbox" id="ack-checkbox" onchange="onAckChange()" disabled />
                            <span>I confirm that I have read, understood, and accept this offer letter and all terms and conditions herein.</span>
                        </label>
                    </div>

                    {{-- Signature Pad Card (Opens upon clicking Sign) --}}
                    <div class="sig-pad-card" id="sig-pad-card" style="display: none;">
                        <div class="sig-pad-title">Candidate Digital Signature</div>
                        <div class="sig-pad-subtitle">Sign inside the box below using your mouse, trackpad, or touch screen</div>
                        <div class="sig-pad-wrap">
                            <canvas id="signature-pad" class="sig-canvas"></canvas>
                        </div>
                        <div class="sig-pad-actions">
                            <button type="button" class="btn-clear-sig" onclick="clearSignature()">Clear Signature</button>
                            <span class="sig-hint" id="sig-hint">Draw your signature above</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="sign-actions-wrap">
                        <button type="button" id="accept-sign-btn" class="btn-sign-primary" disabled onclick="openSignaturePad()">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Accept &amp; Sign Document
                        </button>

                        <button type="button" id="submit-btn" class="btn-sign-submit" style="display:none;" disabled onclick="submitOffer()">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            Confirm &amp; Submit Offer Acceptance
                        </button>
                    </div>
                </div>
            @endif

        </div>

        {{-- Marker for intersection observer (end of document) --}}
        <div id="document-end" style="height: 1px; width: 100%;"></div>

    </div>
</main>

{{-- Success overlay modal --}}
<div class="success-overlay" id="success-overlay" style="display:none;">
    <div class="success-card">
        <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2 class="success-title">Offer Letter Accepted!</h2>
        <p class="success-msg">Thank you, <strong>{{ $offer->candidate_name }}</strong>. Your offer letter has been digitally signed and accepted. A copy has been generated and securely archived.</p>
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <a href="#" id="download-signed-link" class="btn-sign-primary" style="text-decoration:none;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Signed Offer Letter
            </a>
            <button type="button" class="btn-clear-sig" onclick="location.reload();">
                View Completed Document
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const TOKEN = '{{ $offer->token }}';
const CSRF  = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

let signaturePad = null;
let readingDone  = false;
let ackChecked   = false;

// ── Reading / Scroll Tracking ──────────────────────────────────────────
const progressBar   = document.getElementById('progress-bar');
const progressLabel = document.getElementById('progress-label');
const endMarker     = document.getElementById('document-end');

function updateProgress() {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const docH      = document.documentElement.scrollHeight - window.innerHeight;
    if (docH <= 0) return;
    const pct       = Math.min(100, Math.round((scrollTop / docH) * 100));
    if (progressBar) progressBar.style.width = pct + '%';
    if (progressLabel) progressLabel.textContent = pct + '% Read';
}

const endObserver = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting && !readingDone) {
        readingDone = true;
        if (progressBar) progressBar.style.width = '100%';
        if (progressLabel) {
            progressLabel.textContent = '100% Read — Ready to Sign';
            progressLabel.style.color = '#28a745';
        }
        
        // Unlock acknowledgment checkbox
        const cb = document.getElementById('ack-checkbox');
        const lbl = document.getElementById('ack-check-label');
        if (cb && lbl) {
            cb.disabled = false;
            lbl.classList.remove('disabled');
        }
        
        document.getElementById('reading-reminder')?.remove();

        // Notify server that reading is complete
        fetch(`/offer/${TOKEN}/reading-complete`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
        }).catch(() => {});
    }
}, { threshold: 0.8 });

if (endMarker) endObserver.observe(endMarker);
window.addEventListener('scroll', updateProgress, { passive: true });

// ── Acknowledgment change ──────────────────────────────────────────────
function onAckChange() {
    ackChecked = document.getElementById('ack-checkbox')?.checked || false;
    const btn = document.getElementById('accept-sign-btn');
    if (btn) btn.disabled = !ackChecked;
}

// ── Signature Pad ──────────────────────────────────────────────────────
function openSignaturePad() {
    const padCard = document.getElementById('sig-pad-card');
    padCard.style.display = 'block';
    document.getElementById('accept-sign-btn').style.display = 'none';
    document.getElementById('submit-btn').style.display = 'inline-flex';
    initSignaturePad();
    padCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function initSignaturePad() {
    if (signaturePad) return;
    const canvas = document.getElementById('signature-pad');
    resizeCanvas(canvas);
    signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255,255,255,0)',
        penColor: '#000000',
        minWidth: 1.5,
        maxWidth: 3.5,
    });
    signaturePad.addEventListener('endStroke', onSigEnd);
    window.addEventListener('resize', () => resizeCanvas(canvas));
}

function resizeCanvas(canvas) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width  = canvas.offsetWidth  * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    if (signaturePad) signaturePad.clear();
}

function onSigEnd() {
    if (!signaturePad.isEmpty()) {
        const hint = document.getElementById('sig-hint');
        hint.textContent = 'Signature captured ✓';
        hint.style.color = '#28a745';
        const submitBtn = document.getElementById('submit-btn');
        if (submitBtn) submitBtn.disabled = false;
    }
}

function clearSignature() {
    if (signaturePad) signaturePad.clear();
    const hint = document.getElementById('sig-hint');
    hint.textContent = 'Draw your signature above';
    hint.style.color = '';
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) submitBtn.disabled = true;
}

// ── Submit ─────────────────────────────────────────────────────────────
async function submitOffer() {
    if (!readingDone || !ackChecked || signaturePad?.isEmpty()) {
        alert('Please complete all required steps: read the full document, accept the terms, and draw your signature.');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    const sigData = signaturePad.toDataURL('image/png');

    try {
        const resp = await fetch(`/offer/${TOKEN}/sign`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                signature_data: sigData,
                acknowledged: true,
            }),
        });

        const data = await resp.json();

        if (data.ok) {
            document.getElementById('download-signed-link').href = data.signed_url;
            document.getElementById('success-overlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } else {
            alert(data.message || 'An error occurred while submitting your signature. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Confirm & Submit Offer Acceptance';
        }
    } catch (e) {
        alert('Network error. Please check your connection and try again.');
        btn.disabled = false;
        btn.textContent = 'Confirm & Submit Offer Acceptance';
    }
}
</script>
</body>
</html>
