<form method="post" action="<?= $params['process_url'] ?>" class="flex--grow">
    <div class="styled-search styled-search--only_keyword">
        <input type="text" name="keywords"
               placeholder="<?= $params['placeholder'] ? $params['placeholder'] : 'Enter keywords to search' ?>"
               value="<?= $this->safeString($params['search_keywords']) ?>"/>
        <button type="submit"><?= $this->getIcon('search') ?></button>
    </div>
</form>