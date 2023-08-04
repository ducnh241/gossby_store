<?php

class Helper_LiveChat_Common extends OSC_Object
{
    public function createTicketFreshDesk($data_request) {
        $result = false;

        try {
            $eol = "\r\n";
            $mime_boundary = md5(time());

            $data = '--' . $mime_boundary . $eol;
            $data .= 'Content-Disposition: form-data; name="name"' . $eol . $eol;
            $data .= $data_request['name'] . $eol;

            $data .= '--' . $mime_boundary . $eol;
            $data .= 'Content-Disposition: form-data; name="email"' . $eol . $eol;
            $data .= $data_request['email'] . $eol;

            $data .= '--' . $mime_boundary . $eol;
            $data .= 'Content-Disposition: form-data; name="subject"' . $eol . $eol;
            $data .= $data_request['subject'] . $eol;

            $data .= '--' . $mime_boundary . $eol;
            $data .= 'Content-Disposition: form-data; name="priority"' . $eol . $eol;
            $data .= '2' . $eol;

            $data .= '--' . $mime_boundary . $eol;
            $data .= 'Content-Disposition: form-data; name="status"' . $eol . $eol;
            $data .= '2' . $eol;


            $data .= '--' . $mime_boundary . $eol;
            $data .= 'Content-Disposition: form-data; name="description"' . $eol . $eol;
            $data .= $data_request['description'] . $eol;

            if (!empty($data_request['attachments']) && is_array($data_request['attachments'])) {
                foreach ($data_request['attachments'] as $attachment) {
                    $tmpfile = OSC_Storage::tmpGetFilePath($attachment);
                    $mimetype = mime_content_type($tmpfile);
                    $filename = basename(OSC_Storage::tmpGetFilePath($attachment));

                    $data .= '--' . $mime_boundary . $eol;
                    $data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' . $filename . '"' . $eol;
                    $data .= "Content-Type: $mimetype" . $eol . $eol;
                    $data .= file_get_contents($tmpfile) . $eol;
                }

                $data .= '--' . $mime_boundary . $eol;
                $data .= 'Content-Disposition: form-data; name="tags[]"' . $eol . $eol;
                $data .= 'with_photo' . $eol;
            }

            foreach ($data_request['tags'] as $tag) {
                $data .= '--' . $mime_boundary . $eol;
                $data .= 'Content-Disposition: form-data; name="tags[]"' . $eol . $eol;
                $data .= $tag . $eol;
            }

            $data .= '--' . $mime_boundary . '--' . $eol . $eol;

            $header[] = 'Content-type: multipart/form-data; boundary=' . $mime_boundary;

            $url = FRESHDESK_DOMAIN . '/api/v2/tickets';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_USERPWD, FRESHDESK_API_KEY . ':' . FRESHDESK_API_PASSWORD);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            $info = curl_getinfo($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($server_output, 0, $header_size);
            $response = substr($server_output, $header_size);

            if ($info['http_code'] == 201) {
                $result = true;
            }

            curl_close($ch);
        } catch (Exception $ex) {
        }
        return $result;
    }
}
