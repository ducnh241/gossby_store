<?php
/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
$lang = OSC::core('language')->get();

$member_id = $params['element']['model']->getId();

$this->addComponent('cropper', 'uploader')
        ->push('/script/core/user/avatar.js', 'js')
        ->push('user/avatar.css', 'css')
        ->push(<<<EOF
$('#{$params['element']['id']}').osc_avaUploader({
    upload_url : '{$this->getUrl('user/backend_member_avatar/upload', array('id' => $member_id))}',
    crop_url : '{$this->getUrl('user/backend_member_avatar/crop', array('id' => $member_id))}',
    remove_url : '{$this->getUrl('user/backend_member_avatar/remove', array('id' => $member_id))}',
    cancel_url : '{$this->getUrl('user/backend_member_avatar/removeTemporary', array('id' => $member_id))}',
    ava_url : '{$params['element']['model']->getAvatarUrl(Model_User_Member::AVA_EXTRA_SIZE)}',
    ava_extension : '{$params['element']['model']->data['avatar_extension']}',
    lang : {
        cropper_frm_title : '{$lang['usr.ava_cropper']}',
        confirm_close_cropper : '{$lang['usr.confirm_close_ava_cropper']}',
        confirm_remove_ava : '{$lang['usr.confirm_rmv_ava']}'
    }
});
EOF
, 'js_code');
?>
<tr class="field-row">
    <td class="field-cell center nobrdbottom brdright p15" rowspan="4" style="width: 100px" id="<?php echo $params['element']['id']; ?>"></td>
    <td colspan="2" style="display: none">&nbsp;</td>
</tr>
