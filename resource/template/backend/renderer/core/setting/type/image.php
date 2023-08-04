<?php $this->push('core/setting.js', 'js'); ?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
<div>
    <div data-insert-cb="initSettingType__Image" data-max-file-size="<?= $params['max_file_size'] ?? '' ?>" data-upload-url="<?= $this->getUrl('core/backend_setting/imageUpload', ['key' => $params['key']]) ?>" data-input="config[<?= $params['key'] ?>]" data-image="<?= is_array($params['value']) ? OSC::core('aws_s3')->getStorageUrl($params['value']['file']) : '' ?>" data-value="<?= $this->safeString(OSC::encode($params['value'])) ?>"></div>
</div>
