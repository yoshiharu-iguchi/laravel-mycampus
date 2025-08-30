<p>{{ $student->name }} 様</p>

<p>学生登録が完了しました。以下のURLから保護者情報をご登録ください。</p>

<p><a href="{{ $inviteUrl }}">{{ $inviteUrl }}</a></p>

<p>※このURLは{{ $student->name }}様専用です。他の方に共有しないでください。</p>