<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
@page {
    margin: 0px;
    size: 612pt 792pt; /* Standard US Letter as in original reference PDF */
}
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8.5pt;
    line-height: 1.35;
    color: #000000;
}
.bg-letterhead {
    position: fixed;
    top: 0;
    left: 0;
    width: 612pt;
    height: 792pt;
    z-index: -1000;
}
.page {
    padding: 142pt 52pt 30pt 52pt; /* 142pt starts exactly below the letterhead header */
    height: 792pt;
    position: relative;
    page-break-after: always;
}
.page:last-child {
    page-break-after: avoid;
}
.ar {
    direction: rtl;
    text-align: right;
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 8pt;
    margin-top: 3pt;
    margin-bottom: 5pt;
}
.bold { font-weight: bold; }
.center { text-align: center; }
.right { text-align: right; }
.underline { text-decoration: underline; }
.mb-2 { margin-bottom: 2pt; }
.mb-4 { margin-bottom: 4pt; }
.mb-6 { margin-bottom: 6pt; }
.mb-8 { margin-bottom: 8pt; }
.mt-4 { margin-top: 4pt; }
.mt-6 { margin-top: 6pt; }
</style>
</head>
<body>

{{-- Fixed Letterhead Background Image on Every Page --}}
<img src="{{ public_path('images/fts_letterhead.jpg') }}" class="bg-letterhead" />

{{-- PAGE 1 --}}
<div class="page">
    <div class="center mb-2" style="font-size: 7.5pt;">Rev-06-23</div>
    <div class="right bold mb-4">Date: {{ $offer->offer_date->format('d/m/Y') }}</div>

    <table style="width: 100%; margin-bottom: 4pt;">
        <tr>
            <td class="bold">To: Mr./Ms. {{ strtoupper($offer->candidate_name) }}</td>
            <td class="right" style="font-size: 8pt;">Page 1 of 5</td>
        </tr>
    </table>
    <div class="mb-2">Passport NO: {{ $offer->passport_number }}</div>
    <div class="mb-8">Nationality: - {{ $offer->nationality }}</div>

    <div class="center bold underline mb-2" style="font-size: 9.5pt;">Sub: Letter of Appointment</div>
    <div class="center bold underline mb-8" style="font-size: 9pt;">Validity of this offer letter: {{ $offer->validity_date->format('d/m/Y') }}</div>

    <div class="mb-4">
        We take pleasure to inform you that, management has decided to appoint you as a <strong>{{ $offer->designation }}</strong> on the following terms and conditions.
    </div>

    <div class="ar bold underline">خطاب التعيين</div>
    <div class="ar mb-8">
        يسرّنا إبلاغكم بأن الإدارة قد قررت تعيينكم في منصب {{ $offer->designation }} وفقًا للشروط والأحكام التالية:
    </div>

    <div class="bold mb-2">01: Place of posting</div>
    <div class="mb-2">
        Your posting will be at {{ strtoupper($offer->place_of_posting) }} for the present. However, during the employment with the company, you may be posted/transferred to any of the offices at different locations in United Arab Emirates.
    </div>
    <div class="ar bold">01 / مكان العمل</div>
    <div class="ar mb-8">
        سيكون مقر عملك في {{ $offer->place_of_posting }} في الوقت الحالي. ومع ذلك، خلال فترة عملك لدى الشركة، يجوز تكليفك بالعمل أو نقلك إلى أي من مكاتب الشركة في مواقع مختلفة داخل دولة الإمارات العربية المتحدة.
    </div>

    <div class="bold mb-2">02: Working hours</div>
    <div class="mb-2">{{ $offer->working_hours ?? '8:00 AM to 5:00 PM including 1hour break (Differs accordingly)' }}</div>
    <div class="mb-2">{{ $offer->weekly_day_off ? $offer->weekly_day_off . ' – Weekly Day Off' : 'Sunday – Weekly Day Off' }}</div>
    <div>{{ $offer->overtime_info ?? '08 Working Hours can be changed as per the site conditions & Extra Hours will be paid as Over Time.' }}</div>
</div>

{{-- PAGE 2 --}}
<div class="page">
    <div class="right mb-4" style="font-size: 8pt;">Page 2 of 5</div>

    <div class="ar bold">02 / ساعات العمل</div>
    <div class="ar">
        قد تختلف ساعات العمل حسب ظروف العمل) من الساعة 8:00 صباحًا إلى الساعة 5:00 مساءً، بما في ذلك استراحة لمدة ساعة واحدة.
    </div>
    <div class="ar">يوم الأحد – يوم الراحة الأسبوعية</div>
    <div class="ar mb-6">
        يبلغ عدد ساعات العمل 8 ساعات يوميًا ، ويجوز تعديل ساعات العمل وفقًا لظروف ومتطلبات موقع العمل، كما سيتم احتساب ودفع أي ساعات عمل إضافية كـ ساعات عمل إضافية (Overtime).
    </div>

    <div class="bold mb-2">03: Salary &amp; Allowance</div>
    <div class="mb-2">(3a) Basic Salary: {{ number_format($offer->basic_salary, 0) }} AED (Arab Emirates Dirham {{ strtoupper($offer->basic_salary_words) }})</div>
    <div class="mb-2">Other Allowances: {{ number_format($offer->other_allowances, 0) }} AED (Arab Emirates Dirham {{ strtoupper($offer->other_allowances_words) }})</div>
    <div class="bold mb-4">Total Salary: {{ number_format($offer->total_salary, 0) }} AED (Arab Emirates Dirham {{ strtoupper($offer->total_salary_words) }})</div>

    <div class="bold mb-2">(3b) Company Allowances.</div>
    <div class="mb-2">Company will provide you</div>
    <div class="mb-4">
        1. 30 days paid annual leave. Air ticket allowance is provided once every two years, up to a maximum of AED 1,500, upon submission of a confirmed flight ticket copy to HR for reimbursement. You will be also entitled to other allowances/perquisites/facilities as per the labor law of United Arab Emirates &amp; the policy of the company as per Standard operation procedure of FTS.
    </div>

    <div class="ar bold">(03) الراتب والبدلات</div>
    <div class="ar">(3:أ) الراتب الأساسي: {{ number_format($offer->basic_salary, 0) }} درهمًا إماراتيًا ({{ $offer->basic_salary_words_ar ?? 'خمسمائة وخمسون درهمًا إماراتيًا فقط لا غير' }}).</div>
    <div class="ar">البدلات الأخرى: {{ number_format($offer->other_allowances, 0) }} درهمًا إماراتيًا ({{ $offer->other_allowances_words_ar ?? 'ألفان وأربعمائة وخمسون درهمًا إماراتيًا فقط لا غير' }}).</div>
    <div class="ar">إجمالي الراتب: {{ number_format($offer->total_salary, 0) }} درهم إماراتي ({{ $offer->total_salary_words_ar ?? 'ثلاثة آلاف درهم إماراتي فقط لا غير' }}).</div>
    <div class="ar bold mt-4">(3:ب) بدلات الشركة:</div>
    <div class="ar mb-6">
        الإجازة السنوية: يحق لك الحصول على إجازة سنوية مدفوعة الأجر لمدة 30 يومًا , بدل تذكرة الطيران: يتم توفير بدل تذكرة سفر مره واحده كل سنتين بحد اقصي 1500 درهم اماراتي , وذلك عند تقديم نسخة من تذكرة الطيران المؤكده الي قسم الموارد البشريه لاسترداد قسمة التذكره. كما يحق لك الحصول على البدلات والمزايا والتسهيلات الأخرى وفقًا لقانون العمل في دولة الإمارات العربية المتحدة، وكذلك وفقًا لسياسات الشركة واجراءات التشغيل القياسية المعتمده لدي الشركة.
    </div>

    <div class="bold mb-2">04: Increments</div>
    <div>
        Your increments and future prospects in the company shall entirely depend on your efficiency, hard work, sincerity, good conduct and such other relevant factors. Increment in no case shall be automatic and/ or a matter of right.
    </div>
</div>

{{-- PAGE 3 --}}
<div class="page">
    <div class="right mb-4" style="font-size: 8pt;">Page 3 of 5</div>

    <div class="ar bold">04 / الزيادات</div>
    <div class="ar mb-6">
        تعتمد الزيادات والتوقعات المستقبلية في الشركة اعتمادًا كليًا على كفاءتك وعملك الجاد وإخلاصك وحسن سلوكك وغيرها من العوامل ذات الصلة. لا تكون الزيادة في أي حال تلقائية و / أو مسألة حق.
    </div>

    <div class="bold mb-2">05: Medical fitness and verification of fitness</div>
    <div class="mb-2">Your appointment is subjected to:</div>
    <div style="padding-left: 12pt;" class="mb-4">
        <div>a. Medical fitness by approved medical officer specified by the Government of UAE.</div>
        <div>b. The management has the right to get you medically examined by any certified medical practitioner during the period of your service. In case you are found medically unfit to continue with the job, you will lose to lien on the job.</div>
    </div>

    <div class="ar bold">05 / اللياقة الطبية والتحقق من اللياقة البدنية</div>
    <div class="ar">يخضع تعيينك إلى:</div>
    <div class="ar">ا. اللياقة الطبية من قبل مسؤول طبي معتمد من قبل حكومة الإمارات العربية المتحدة.</div>
    <div class="ar mb-6">ب. الإدارة لها الحق في إجراء فحص طبي لك من قبل أي ممارس طبي معتمد خلال فترة خدمتك. في حال وجدت أنك غير لائق طبيا لمواصلة العمل فسوف تخسر مقابل الحصول على الوظيفة.</div>

    <div class="bold mb-2">06. Termination of permanent service</div>
    <div class="mb-2">6a) At present your will be in probation for {{ $offer->probation_period ?? '6 months' }} and the permanent contract will be for {{ $offer->contract_duration ?? '2 years' }}.</div>
    <div class="mb-2">(6b) In cases of misconduct, disloyalty, Commission of an act involving moral turpitude will cause immediate action by company interest.</div>
    <div class="mb-2">(6c)) In case the contract is broken for any reason from company side or labor side before finishing 1 year, he should pay emoluments of AED 5000/- (Five Thousand Arab Emirate Dirham) In return for all fees and expenses spent on preparing the worker for work and conforming to the conditions of civil defense such as examinations, training of the worker and arranging the official dress.</div>
    <div class="mb-2">(6d) In case the contract is broken for any reason from company side or labor side before finishing the 2-yearcontract period, he should pay emoluments of AED 2500/- (Two Thousand Five Hundred Arab Emirate Dirham) In return for all fees and expenses spent on preparing the worker for work and conforming to the conditions of civil defense such as examinations, training of the worker and arranging the official dress.</div>
    <div>(6e) However if there is a discrepancy in the copies of documents or certificates given by you as a proof of above, we retain the right to review our offer of employment contract and the employment have to pay the fine AED 10,000 along with fines and legal expenses which are received from the authorities on company’s name because of the same.</div>
</div>

{{-- PAGE 4 --}}
<div class="page">
    <div class="right mb-4" style="font-size: 8pt;">Page 4 of 5</div>

    <div class="ar bold">06 / إنهاء الخدمة الدائمة</div>
    <div class="ar">6 أ) في الوقت الحالي سوف تكون قيد الاختبار لمدة 6 أشهر وسيكون العقد الدائم لمدة عامين.</div>
    <div class="ar">(6 ب) في حالات سوء السلوك عدم الولاء فإن ارتكاب فعل ينطوي على ضرر أخلاقي يؤدي إلى اتخاذ إجراءات فورية من جانب مصلحة الشركة.</div>
    <div class="ar">(6 ج) في حالة رغبة الموظف في فسخ العقد لأي سبب من الأسباب أو رغبة الشركة بفسخ العقد بسبب تحصل العامل على ثلاثة انذارات خطية او اكثر قبل الانتهاء من عام واحد من مدة العقد ، يجب عليه دفع غرامة قدرها 5000 درهم - (خمسة آلاف درهم إماراتي) لقاء كل الاتعاب و النفقات التي انفقت على تجهيز العامل للعمل و مطابقة شروط الدفاع المدني كامتحان و تدريب العامل و تامين الباس الرسمي.</div>
    <div class="ar">(6 د) في حالة رغبة الموظف في فسخ العقد لأي سبب من الأسباب أو رغبة الشركة بفسخ العقد بسبب تحصل العامل على ثلاثة انذارات خطية او اكثر قبل الانتهاء من مدة العقد ، يجب عليه دفع غرامة بقيمة 2500 (درهم إماراتي - ألفان وخمسمائة درهم إماراتي) لقاء كل الاتعاب و النفقات التي انفقت على تجهيز العامل للعمل و مطابقة شروط الدفاع المدني كامتحان و تدريب العامل و تامين الباس الرسمي.</div>
    <div class="ar mb-6">(6 هـ) ومع ذلك ، إذا كان هناك تعارض في نسخ المستندات أو الشهادات التي قدمتها كدليل أعلاه ، فإننا نحتفظ بالحق في مراجعة عرضنا الخاص بعقد العمل وعلى العامل دفع غرامة قدرها 10،000 درهم.</div>

    <div class="bold mb-2">07 General</div>
    <div class="mb-2">(7a) The service rules and regulations including conduct, discipline and administrative orders will cover you and any such other rules or orders of the company that may be in force from time to time.</div>
    <div class="mb-2">(7b) You will hand over the charge of letter of authority or power of attorney issued to you or any property/material if the company in your possession at the time of cessation of your employment within the company.</div>
    <div class="mb-2">(7c) By signing this Contract/Offer Letter the employee is confirming the reading, understanding &amp; accepting of all the FIRE TECHNICAL SERVICES (FTS) Terms and Condition &amp; STANDARD OPERATION PROCEDURE (SOP) with all its revision till the date of this Contract/Offer Letter.</div>
    <div class="mb-4">(7d) After completing or cancelling the Contract with FTS, the employee is not permitted work with FTS clients or sister concern companies without a written acceptance from FTS.</div>

    <div class="ar bold">07 / عام</div>
    <div class="ar">(7 أ) ستغطي قواعد ولوائح الخدمة بما في ذلك السلوك والانضباط والأوامر الإدارية أي قواعد أو أوامر أخرى للشركة قد تكون سارية من وقت لآخر.</div>
    <div class="ar">(7 ب) سوف تقوم بتسليم أي خطاب تفويض أو توكيل صادر إليك أو أي ممتلكات / مواد إذا كانت بحوزتك من قبل الشركة وقت توقف عملك داخل الشركة.</div>
    <div class="ar">(7 ج) بتوقيع هذا العقد ، يجب على كل موظف اتباع أساس إجراءات التشغيل القياسية والملحق ذي الصلة من الشركة.</div>
    <div class="ar">(7 د) بعد الإنتهاء أو إلغاء العقد مع الشركة , لا يحق للموظف العمل مع عملاء الشركة أو الشركات الشقيقة المعنية دون موافقة خطية من الشركة.</div>
</div>

{{-- PAGE 5 --}}
<div class="page">
    <div class="right mb-4" style="font-size: 8pt;">Page 5 of 5</div>

    <div class="bold mb-2">08 Joining date</div>
    <div class="mb-2">{{ $offer->joining_date ? 'You will report to duty on ' . $offer->joining_date->format('d/m/Y') : 'You will report to duty once the work permit is issued' }}</div>
    <div class="ar bold">08 / تاريخ الانضمام</div>
    <div class="ar mb-6">بمجرد إصدار التأشيرة.</div>

    <div class="bold mb-2">09 Cancellation of Contract</div>
    <div class="mb-2">In case the employee is willing to break the contract and leave the company, the employee should give a two months’ notice prior from leaving the company.</div>
    <div class="ar mb-6">في حالة استعداد الموظف لكسر العقد ومغادرة الشركة يجب على الموظف تقديم إشعار قبل شهرين من مغادرة الشركة.</div>

    @if(!empty($offer->additional_terms))
    <div class="bold mb-2">{{ $offer->additional_terms_title ?: '10. Additional Terms & Special Conditions' }}</div>
    <div class="mb-6" style="white-space: pre-line; font-size: 8pt; line-height: 1.35;">{!! nl2br(e($offer->additional_terms)) !!}</div>
    @endif

    <div class="center mt-4">We look forward a long, successful and pleasant association with us</div>
    <div class="ar center mb-6">ونحن نتطلع إلى وجود علاقة طويلة وناجحة وممتعة معنا</div>

    <div class="ar" style="text-align: left; margin-bottom: 2pt;">فاير للخدمات التقنية</div>
    <div class="bold">For Fire Technical Services</div>
    <div style="margin-top: 25pt;">(Signature of owner/Managing Director)</div>
    <hr style="border: none; border-top: 1px solid #000; margin: 10pt 0 14pt 0;">

    <div class="center bold mb-4" style="font-size: 9.5pt;">Acknowledgement and Acceptance</div>
    <div class="mb-2" style="font-size: 8pt;">
        I have read and understood the above terms and conditions and FTS SOP with all its revision and unconditionally accept the same.
    </div>
    <div class="ar mb-6" style="font-size: 8pt;">
        لقد قرأت وفهمت جميع الشروط والأحكام المذكورة أعلاه، وكذلك إجراءات التشغيل القياسية (SOP) الخاصة بشركة FTS بجميع تعديلاتها وأوافق عليها دون قيد أو شرط
    </div>

    <div class="bold mb-2">Name: Mr./Ms. {{ strtoupper($offer->candidate_name) }}</div>
    <div class="bold mb-6">Passport No: {{ $offer->passport_number }}</div>

    {{-- The ONLY signature and fingerprint block on the letterhead --}}
    <table style="width: 100%; margin-top: 10pt;">
        <tr>
            <td style="width: 40%; vertical-align: bottom;">
                <strong>Signature:</strong>
            </td>
            <td style="width: 30%; vertical-align: bottom;">
                <strong>Finger Print:</strong>
            </td>
            <td style="width: 30%; vertical-align: bottom; text-align: right;">
                <strong>Date:</strong>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
