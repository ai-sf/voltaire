<?php

return (object) [
  "use_emailer" => getenv('USE_EMAILER') !== false ? filter_var(getenv('USE_EMAILER'), FILTER_VALIDATE_BOOLEAN) : true,
  "username" => getenv('EMAIL_USER') ?: "noreply@ai-sf.it",
  "host" => getenv('EMAIL_HOST') ?: "smtp.gmail.com",
  "password" => getenv('EMAIL_PASS') ?: "aisfBN811DR",

  "replyTo" => getenv('EMAIL_REPLYTO') ?: "esecutivo@ai-sf.it",
  "replyToName" => getenv('EMAIL_REPLYTO_NAME') ?: "Esecutivo AISF",
  "fromName" => getenv('EMAIL_FROM_NAME') ?: "AISF"
];
