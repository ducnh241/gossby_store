<?php

// mất breadcrumb nếu extend Abstract_Backend_Controller
class Helper_Core_Form {

    public function renderer($params, $multi = false) {
        $type = $params['type'];
		if(!$type) return;
		switch ($type) {
            case "input":
                $this->input($params, $multi);
                break;
            case "select":
                $this->select($params, $multi);
                break;
			case "textarea":
				$this->textarea($params, $multi);
				break;
			case "file":
				$this->file($params, $multi);
				break;
			case "editor":
				$this->editor($params, $multi);
				break;
			case "onoff":
				$this->onoff($params, $multi);
				break;
			default:
				$this->input($params, $multi);
				break;
		}
    }

	public function input($params, $multi) {
        $setting_key_hash = OSC::makeUniqid();
    ?>
		<div>
			<?php if ($params['label']) : ?><label for="input-<?= $setting_key_hash ?>"><?= $params['label'] ?></label><?php endif; ?>
			<div>
				<input type="text" name="<?php echo ($multi)?$multi."[".$params['name']."]":$params['name']; ?><?php echo ($multi)?'[]':''; ?>" class="styled-input" id="input-<?= $setting_key_hash ?>" value="<?= $params['value']; ?>" />
				<?php if ($params['description']) : ?>
					<div class="input-desc"><?= $params['description'] ?></div>
				<?php endif; ?>
			</div>
		</div>
    <?php
	}
    public function select($params, $multi) {
        $setting_key_hash = OSC::makeUniqid();
    ?>
        <div>
            <?php if ($params['label']) : ?><label for="input-<?= $setting_key_hash ?>"><?= $params['label'] ?></label><?php endif; ?>
            <div>
                <select name="<?php echo ($multi) ? $multi . "[" . $params['name'] . "]" : $params['name']; ?><?php echo ($multi) ? '[]' : ''; ?>"
                        class="select2__form styled-input" id="input-<?= $setting_key_hash ?>" data-insert-cb="initSelect2Form">
                    <option value="">Select an option</option>
                    <?php

                    foreach ($params['options'] as $key => $option):
                        $selected = ($key == $params['value']) ? 'selected=selected' : '';
                        echo "<option value='" . $key . "' " . $selected . ">" . $option . "</option>";
                    endforeach;
                    ?>
                </select>
                <?php if ($params['description']) : ?>
                    <div class="input-desc"><?= $params['description'] ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php

    }
    public function textarea($params) {

    }
	public function file($params, $multi) {
		$setting_key_hash = OSC::makeUniqid();
        ?>
        <div>
	        <?php if ($params['label']) : ?><label for="input-<?= $setting_key_hash ?>"><?= $params['label'] ?></label><?php endif; ?>
            <div class="section-image-uploader">
                <div class="preview" data-image-src="" style="<?php echo ($params['value'])?"background-image: url(".OSC::core('aws_s3')->getStorageUrl($params['value']).")":''; ?>"></div>
                <input class="update-value" type="hidden" name="<?php echo ($multi)?$multi."[".$params['name']."]":$params['name']; ?><?php echo ($multi)?'[]':''; ?>" value="<?= $params['value']; ?>"/>
                <div class="mt10" data-insert-cb="initBackendHomepageSectionBannerUpload" data-process-url="<?= OSC::getUrl('frontend/backend/uploadImage') ?>"></div>
            </div>
        </div>
    <?php

	}
	public function editor($params) {

	}
	public function onoff($params) {

	}
}
