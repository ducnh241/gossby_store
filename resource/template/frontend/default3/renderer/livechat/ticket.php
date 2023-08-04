<?php
/* @var $this Helper_Frontend_Template */
    if (isset($params['message']) && $params['message'] == 1) { ?>
        <script>
            window.onMomentAsyncInit = function(createMomentsSDK) {
                createMomentsSDK().then(() => {
                    MomentsSDK.close();
                });
            };
        </script>
        <script src="https://cdn.livechat-static.com/moments-sdk/moments-sdk-1.0.1.umd.min.js"></script>
        <?php return;
    }
?>
<?php
$this->push('liveChat/ticket.js', 'js')->addComponent('uploader');
$this->push(<<<EOF
    .input_c{
        line-height: 35px;
        width: 100%;
        margin-bottom: 5px;
        border: 1px solid #ccc;
        padding: 0 10px;
        border-radius: 5px;
    }
    .textarea_c{
        border: 1px solid #ccc;
        padding: 10px;
        width: 100%;
        margin-bottom: 5px;
        height: 100px;
        border-radius: 5px;
    }
    .attachment_c{
        border: 1px solid #ccc;
        padding: 10px;
        width: 100%;
        margin-bottom: 5px;
        border-radius: 5px;
    }
    .btn_c{
        display: block;
        width: 100%;
        line-height: 35px;
        background-color: cadetblue;
        color: #fff;
        border: 0;
        border-radius: 5px;
        font-weight: bold;
        text-transform: uppercase;
        cursor: pointer;
    }
    .error-message {
        color: red;
        font-size: 12px;
    }
EOF
    , 'css_code');

$email = $params['email'];
$fullname = $params['fullname'];
$title = $params['title'];
$tag = $params['tag'];
?>

<div id='app' style='display:none;'></div>
<form id='testChatbox' action='' method="post" enctype="multipart/form-data">
    <input type="hidden" name='type' value="post_form">
    <div class="m5">
        <input class='input_c' id='title' type="text" name='title' required="true" placeholder="Title Ticket" name='title' <?php echo ($title) ? 'value="'.$title.'" style="display:none;"' : ''?>>
    </div>
    <div class="m5">
        <input class='input_c' id='email' type="email" name='email' required="true" placeholder="Enter Email" name='email' <?php echo ($email) ? 'value="'.$email.'" style="display:none;"' : ''?>>
    </div>
    <div class="m5">
        <input class='input_c' id='tag' type="text" name='tag' required="true" placeholder="Enter Tag" name='tag' <?php echo ($tag) ? 'value="'.$tag.'" style="display:none;"' : ''?>>
    </div>
    <div class="m5">
        <input class='input_c' id='fullname' type="text" name='fullname' required="true" placeholder="Enter Fullname" name='fullname' <?php echo ($fullname) ? 'value="'.$fullname.'" style="display:none;"' : ''?>>
    </div>
    <div class="m5">
        <textarea class='textarea_c' id='message' name='message' placeholder="Message"></textarea>
    </div>

    <div class="m5" data-insert-cb="initLiveChatAttachment" data-upload-url="<?= $this->getUrl('*/*/uploadAttachment') ?>"></div>
    <?php if (isset($params['message']) && $params['message'] == 0) { ?>
        <span class="m5 error-message">Sorry, an unexpected issue occured from our end. Please try again later.</span>
    <?php } ?>
    <div class="m5">
        <button class='btn_c' type='submit' data-insert-cb="btnCreateTicket">Create ticket</button>
    </div>
</form>

