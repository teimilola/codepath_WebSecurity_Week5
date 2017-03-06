<?php

  require_once('../../private/initialize.php');
  require_once('../../private/crypto_functions.php');

  if(!isset($_GET['id'])) {
    redirect_to('index.php');
  }

  $id = $_GET['id'];
  $agent_result = find_agent_by_id($id);
  $agent = db_fetch_assoc($agent_result);

  $message_result = find_messages_for($agent['id']);
?>

<!doctype html>

<html lang="en">
  <head>
    <title>Messages</title>
    <meta charset="utf-8">
    <meta name="description" content="">
    <link rel="stylesheet" media="all" href="<?php echo DOC_ROOT . '/includes/styles.css'; ?>" />
  </head>
  <body>
    
    <a href="<?php echo url_for('/agents/index.php') ?>">Back to List</a>
    <br/>

    <h1>Messages for <?php echo h($agent['codename']); ?></h1>
    
    <?php if($current_user['id'] == $agent['id']) { ?>
      <p>Your messages are automatically decrypted using your private key.</p>
    <?php } ?>
    
    <table>
      <tr>
        <th>Date</th>
        <th>To</th>
        <th>From</th>
        <th>Message</th>
        <th>Signature</th>
      </tr>
      
      <?php while($message = db_fetch_assoc($message_result)) { ?>
        <?php
          $created_at = strtotime($message['created_at']);
          $sender_result = find_agent_by_id($message['sender_id']);
          $sender = db_fetch_assoc($sender_result);
          $cipher = $message['cipher_text'];
          $signature = $message['signature'];
          if(verify_signature($cipher, $signature, $sender['public_key']) == 1){
            $validity_text = "Valid";
          } else{
            $validity_text = "Not Valid";
          }
          if($message['recipient_id'] == $current_user['id']){
            $message_text = pkey_decrypt($cipher, $agent['private_key']);
          } else{
            $message_text = $cipher;
          }
          
        ?>
        <tr>
          <td><?php echo h(strftime('%b %d, %Y at %H:%M', $created_at)); ?></td>
          <td><?php echo h($agent['codename']); ?></td>
          <td><?php echo h($sender['codename']); ?></td>
          <td class="message"><?php echo h($message_text); ?></td>
          <td class="message"><?php echo h($validity_text); ?></td>
        </tr>
      <?php } ?>
    </table>
    
  </body>
</html>
