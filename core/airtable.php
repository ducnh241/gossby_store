<?php

class OSC_Airtable {

    const API_URL = OSC_AIRTABLE_DOMAIN . '/v0/' . OSC_AIRTABLE_DATABASE_KEY . '/';

    /**
     * @param array $records
     * @param string $table
     * @return mixed
     */
    public function createData(array $records, string $table) {

        return OSC::core('network')->curl(
            self::API_URL . $table, [
            'timeout' => 180,
            'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . OSC_AIRTABLE_API_KEY
            ],
            'json' => [
                'records' => $records
            ],
        ]);
    }

    /**
     * @param array $records
     * @param string $table
     * @return mixed
     */
    public function updateData(array $records, string $table) {

        return OSC::core('network')->curl(
            self::API_URL . $table, [
            'timeout' => 180,
            'request_method' => 'PATCH',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . OSC_AIRTABLE_API_KEY
            ],
            'json' => [
                'records' => $records
            ],
        ]);
    }

    /**
     * @param array $records
     * @param string $table
     * @return mixed
     */
    public function deleteData(array $records, string $table) {

        return OSC::core('network')->curl(
            self::API_URL . $table . '?' . http_build_query(['records' => $records]), [
            'timeout' => 180,
            'request_method' => 'DELETE',
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . OSC_AIRTABLE_API_KEY
            ]
        ]);
    }

    /**
     * document filter https://support.airtable.com/docs/formula-field-reference
     * https://codepen.io/airtable/full/MeXqOg
     * @param array $fields
     * @param string $filter
     * @param array $sorts
     * @param string $table
     * @param int $page_size
     * @param string|null $offset
     * @return mixed
     */
    public function filterData(array $fields, string $filter, array $sorts, string $table, int $page_size = 100, string $offset = null) {

        $query = '';
        if (!empty($fields)) {
            $query .= '?' .  http_build_query(['fields' => $fields]);
        }
        if ($filter) {
            $query .= (strpos($query, '?') !== false ? '&' : '?') . 'filterByFormula=' . urlencode($filter);
        }

        if (!empty($sorts)) {
            $query .= (strpos($query, '?') !== false ? '&' : '?') . http_build_query(['sort' => $sorts]);
        }

        if ($page_size) {
            $query .= (strpos($query, '?') !== false ? '&' : '?') . 'pageSize=' . min($page_size, 100);
        }

        if ($offset) {
            $query .= (strpos($query, '?') !== false ? '&' : '?') . "offset={$offset}";
        }

        return OSC::core('network')->curl(
            self::API_URL . $table . ($query ?? ''), [
            'timeout' => 180,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . OSC_AIRTABLE_API_KEY
            ]
        ]);
    }
}
