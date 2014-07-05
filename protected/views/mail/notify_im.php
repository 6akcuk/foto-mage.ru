<?php
$authors = explode(",", $row['authors']);
$authors_html = array();

foreach ($authors as $_author) {
  $fields = explode(";", $_author);
  // Защита от неполной загрузки данных
  if (isset($fields[2]))
    $authors_html[] = '<a href="http://'. Yii::app()->params['domain'] .'/auth?ticket_id='. $ticket['ticket_id'] .'&token='. $ticket['token'] .'&url=id'. $fields[0] .'" style="color:#005b01;text-decoration: none">'. ActiveHtml::lex(2, $fields[2]) .' '. $fields[1] .'</a>';
}

if ($row['msg_num'] == 1)
  $author = explode(";", $row['authors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title></title>
  <meta charset="UTF-8">
  <style type="text/css">
    a {cursor: pointer;text-decoration: none}
    a:hover {text-decoration: none}
  </style>
</head>
<body style="margin:0px;padding:0px;font-family:tahoma,arial;font-size:12px">
  <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f5f5f5">
    <tr>
      <td width="5%">

      </td>
      <td width="90%" style="padding:15px 0px;">
        <table border="0" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
          <tr>
            <td>
              <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#557132">
                <tr>
                  <td>
                    <a href="http://<?php echo Yii::app()->params['domain'] ?>" style="display:inline-block;color:#ffffff;font-weight:bold;font-size: 20px;padding:10px;text-decoration: none">SPMIX</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <div style="border: 1px solid #70973f; border-top: 0px; font-size: 12px; padding: 18px 18px 13px 18px">
                <h1 style="margin: 2px 0px 15px 0px; border-bottom: 1px solid #70973f; color: #508033; padding: 0px 0px 4px; font-size: 100%"><?php echo Yii::t('app', 'У Вас {n} новое сообщение|У Вас {n} новых сообщения|У Вас {n} новых сообщений', $row['msg_num']) ?></h1>
                <?php if ($row['msg_num'] > 1): ?>
                <p style="max-width:650px;margin:0px;padding:0px;font-size:12px">
                  Здравствуйте, <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=id<?php echo $row['owner_id'] ?>" style="color:#005b01;text-decoration: none"><b><?php echo $row['firstname'] ?></b></a>.
                  Вы получили:
                </p>
                <br>
                <p style="max-width:650px;margin:0px;padding:0px;font-size:12px">
                  <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=mail%3Ffilter=new" style="color:#005b01;text-decoration: none">
                    <?php echo Yii::t('app', '<b>{n}</b> новое сообщение|<b>{n}</b> новых сообщения|<b>{n}</b> новых сообщений', $row['msg_num']) ?>
                  </a>
                  от
                  <?php echo implode(", ", $authors_html) ?><?php if ($row['auth_num'] > sizeof($authors_html)): ?> и еще <?php echo Yii::t('app', '{n} человека|{n} человек|{n} человек', ($row['auth_num'] - sizeof($authors_html))) ?><?php endif; ?>.
                </p>
                <table border="0" cellpadding="0" cellspacing="0" style="padding:10px 0px">
                  <tr>
                    <td>
                      <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=mail%3Ffilter=new" style="background: #447229; color: #ffffff; font-weight: bold; display: inline-block; padding: 7px 10px; text-decoration: none">
                        Прочитать
                      </a>
                    </td>
                  </tr>
                </table>
                <?php else: ?>
                <table cellpadding="0" cellspacing="0" border="0">
                  <tr>
                    <td valign="top" style="padding-right: 10px">
                      <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=id<?php echo $author[0] ?>" target="_blank">
                        <img src="<?php echo ActiveHtml::getImageUrl($row['photo']) ?>" alt="" style="display:block" border="0" />
                      </a>
                    </td>
                    <td>
                      <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td width="70" style="color:#808080;padding-right:10px;" valign="top">От кого:</td>
                          <td style="padding-bottom: 6px">
                            <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=id<?php echo $author[0] ?>" target="_blank" style="color:#005b01;text-decoration:none"><?php echo $author[2] .' '. $author[1] ?></a>
                          </td>
                        </tr>
                        <tr>
                          <td style="color:#808080;padding-right:10px;" valign="top">Сообщение:</td>
                          <td style="padding-bottom: 6px">
                            <?php echo nl2br($row['message']) ?>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div style="width:80px">&nbsp;</div>
                          </td>
                          <td>
                            <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=mail%3Fact=show%26id=<?php echo $row['message_id'] ?>" target="_blank" style="color:#808080;text-decoration:none"><?php echo ActiveHtml::date($row['creation_date']) ?></a>
                          </td>
                        </tr>
                        <tr>
                          <td></td>
                          <td style="padding-top: 14px">
                            <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=mail%3Fact=show%26id=<?php echo $row['message_id'] ?>" style="background: #447229; color: #ffffff; font-weight: bold; display: inline-block; padding: 7px 10px; text-decoration: none">
                              Ответить <?php echo ActiveHtml::lex(3, $author[2]) ?>
                            </a>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <tr>
            <td bgcolor="#f5f5f5" align="center" style="padding: 13px 0px 0px 0px; font-size: 12px;">
              <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=id<?php echo $row['owner_id'] ?>" style="color:#005b01;font-weight:bold;text-decoration: none"><?php echo $row['firstname'] ?></a>,
              Вы можете отменить уведомления на E-Mail в
              <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=notify" style="color:#005b01;text-decoration: none">
                Настройках оповещений
              </a>
            </td>
          </tr>
        </table>
      </td>
      <td width="5%">

      </td>
    </tr>
  </table>
</body>
</html>