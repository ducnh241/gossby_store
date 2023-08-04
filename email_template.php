<?php

echo base64_encode(json_encode([
    'template' => 'postOffice/email/cyberMonday/first',
    'subject' => '{{first_name}}, GOSSBY Cyber Monday Sale is ending soon. Buy 2 Get 35% Off EVERYTHING \'til Midnight!',
    'end_date' => '5/11/2019',
    'priority' => 99//,
   // 'test' => [
 //       ['email' => 'batsatla@gmail.com', 'first_name' => 'Sang', 'last_name' => 'Le']
  //  ]
]));
