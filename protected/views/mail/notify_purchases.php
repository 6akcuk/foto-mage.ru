<?php
$_purchases = explode("<gp>", $row['purchases']);
$_photos = explode("<gphoto>", $row['purchases_photo']);

$purchases = $photos = array();

foreach ($_purchases as $i => $_pc) {
  if ($i > 3) break;

  $pc = explode("<purchase>", $_pc);
  $ph = explode("<photo>", $_photos[$i]);

  // Защита от неполной загрузки данных
  if (isset($pc[2]) && isset($ph[1])) {
    $purchases[] = array('id' => $pc[0], 'name' => $pc[1], 'short' => $pc[2]);
    $photos[$pc[0]] = $ph[1];
  }
}

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
              <h1 style="margin: 2px 0px 15px 0px; border-bottom: 1px solid #70973f; color: #508033; padding: 0px 0px 4px; font-size: 100%"><?php echo Yii::t('app', 'В Вашем городе {n} новая закупка|В Вашем городе {n} новые закупки|В Вашем городе {n} новых закупок', $row['pc_num']) ?></h1>
              <table cellpadding="0" cellspacing="5" border="0">
              <?php $max = min($row['pc_num'], 3); ?>
              <?php for($i=0; $i < $max; $i++): ?>
                <tr>
                  <td valign="top" style="padding-right: 10px">
                    <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=purchase<?php echo $purchases[$i]['id'] ?>" target="_blank">
                      <img src="<?php echo ActiveHtml::getImageUrl($photos[$purchases[$i]['id']]) ?>" alt="" style="display:block" border="0" />
                    </a>
                  </td>
                  <td>
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="padding-bottom: 10px">
                          <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=purchase<?php echo $purchases[$i]['id'] ?>" target="_blank" style="color:#005b01;text-decoration:none"><?php echo $purchases[$i]['name'] ?></a>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding-bottom: 6px">
                          <?php echo nl2br($purchases[$i]['short']) .'..' ?>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding-top: 10px">
                          <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=purchase<?php echo $purchases[$i]['id'] ?>" style="background: #447229; color: #ffffff; font-weight: bold; display: inline-block; padding: 7px 10px; text-decoration: none">
                            Просмотреть закупку
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              <?php endfor; ?>
              </table>
              <?php if ($max < $row['pc_num']): ?>
              <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=purchases" style="display: block; padding: 10px; text-decoration: none; text-align: center; background: #dae1e8; color: #123">
                Просмотреть еще <?php echo Yii::t('app', '{n} закупку|{n} закупки|{n} закупок', ($row['pc_num'] - $max)) ?>
              </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <tr>
          <td bgcolor="#f5f5f5" align="center" style="padding: 13px 0px 0px 0px; font-size: 12px;">
            <a href="http://<?php echo Yii::app()->params['domain'] ?>/auth?ticket_id=<?php echo $ticket['ticket_id'] ?>&token=<?php echo $ticket['token'] ?>&url=id<?php echo $row['user_id'] ?>" style="color:#005b01;font-weight:bold;text-decoration: none"><?php echo $row['firstname'] ?></a>,
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