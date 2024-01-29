Hello {{ $user->firstname }},
<br /><br />
You have been successfully screened as: {{ ($user->status) ? 'APPROVED':'DENIED' }}.
<br /></br />

@if($user->status)
    Your Voting Code is: {{ $user->code }}
    <br /><br />
@endif

Courtesy,
Xst Sch Alumni 98/04 - Electoral Committee
