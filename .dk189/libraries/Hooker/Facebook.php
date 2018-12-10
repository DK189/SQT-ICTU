<?php
namespace Hooker;

class Facebook {
    private $db;
    private $fb_page_token;
    private $cli;

    public function __construct (\Google\Firebase\DB $db, $fb_page_token) {
        $this->db = $db;
        $this->fb_page_token = $fb_page_token;
        $this->cli = new \Curl\Client();
    }

    public function sendMessage ($recipientId, $content) {
        return $this->cli->post(
            "https://graph.facebook.com/v2.6/me/messages?access_token=" . $this->fb_page_token,
            [
                "recipient" => [
                    "id" => "" . $recipientId
                ],
                "message" => [
                    "text" => $content
                ]
            ],
            true // set send as json content
        );
    }

    public function process ($request_id, $data) {
        // return;
        $this->db->set("facebook-bot/logs", $request_id, $data);

        if (\is_object($data)) {
            if ($data->object == "page") {
                foreach ($data->entry as $entry_index => $entry) {
                    if (!!$entry->messaging) {
                        foreach($entry->messaging as $msg) {
                            if (!!$msg->sender && !!$msg->message) {

                                $senderId = $msg->sender->id;
                                $mText = $msg->message->text;
                                $mCmd = \strtoupper(trim($mText));

                                $user = false;
                                $token = false;
                                $semester = false;

                                try {
                                    $resp = $this->db->get("facebook-bot/sqt-ictu/users/", $senderId);
                                    $user = \json_decode($resp->getBody());
                                    if (!!$user) {
                                        $token = $user->token;
                                        $semester = $user->semester->MaKy;

                                        $token = json_decode(decrypt($token));

                                        $Machine = new \TNU\Machine();
                                        if (!$Machine->login($token->username, md5($token->password))) {
                                            throw new \Exception("Error Processing Request", 1);
                                        }
                                    }
                                }catch (\Exception $ex) {
                                    $user = false;
                                    $token = false;
                                    $semester = false;
                                }

                                if (isset($mCmd)) {
                                    switch ($mCmd) {
                                        case 'LOGIN':
                                        case 'ĐĂNG NHẬP':
                                            $loginUrl = \sprintf(
                                                "https://sqt-ictu.herokuapp.com/fblogin.php?sid=%s",
                                                \urlencode(\encrypt(\json_encode([
                                                    "time" => time(),
                                                    "sender" => $senderId
                                                ])))
                                            );
                                            if (!$token) {
                                                $fbresp = self::sendMessage(
                                                    $senderId,
                                                    "Vui lòng truy cập vào đây để đăng nhập:\n"
                                                    . $loginUrl
                                                    . "\n\n"
                                                    . "Sau khi đăng nhập thành công hãy trở lại đây!!\n"
                                                    . "( Lưu ý, hiệu lực đăng nhập chỉ có 24h. )"
                                                );
                                                // $this->db->set("webhook-errors", "fbresp_" . \microtime(true) * 10000, $fbresp->getBody());
                                            } else {
                                                $fbresp = self::sendMessage(
                                                    $senderId,
                                                    "Bạn đã đăng nhập rồi! Nếu muốn đăng nhập lại, hãy nhấn vào đây:\n"
                                                    . $loginUrl
                                                    . "\n\n"
                                                    . "Sau khi đăng nhập thành công hãy trở lại đây!!\n"
                                                    . "( Lưu ý, hiệu lực đăng nhập chỉ có 24h. )"
                                                );
                                            }
                                            break;
                                        case 'TODAY':
                                        case 'HÔM NAY':
                                            if ($token) {
                                                // $tkb = $Machine->getTimeTableOfStudy($semester);
                                                // $lich = [];
                                                // $Subjects = [];
                                                // $homNay = date("Y-m-d", time);
                                                // $result = "Lịch hôm nay (" . $homNay . "):\n\n";
                                                // foreach ($tkb->Subjects as $subj) {
                                                //     $Subjects[$subj->MaMon] = $subj;
                                                // }
                                                // foreach ($tkb->Entries as $tkbEntry) {
                                                //     if ($tkbEntry->Ngay == $homNay) {
                                                //         $lich[] = [
                                                //             "MaMon" => $tkbEntry->MaMon,
                                                //             "TenMon" => $Subjects[$tkbEntry->MaMon]->TenMon,
                                                //             "DiaDiem" => $tkbEntry->DiaDiem,
                                                //             "Tiet" => $tkbEntry->ThoiGian,
                                                //             "HinhThuc" => $tkbEntry->HinhThuc,
                                                //         ];
                                                //         $result .= $Subjects[$tkbEntry->MaMon]->TenMon . "(" . $Subjects[$tkbEntry->MaMon]->SoTinChi . ") - " . $tkbEntry->MaMon;
                                                //         $result .= "Tại: " . $tkbEntry->DiaDiem;
                                                //         $result .= "Tiết: " . $tkbEntry->ThoiGian;
                                                //         $result .= "Hình thức: " . $tkbEntry->HinhThuc;
                                                //         $result .= "\n\n";
                                                //     }
                                                // }

                                                $fbresp = self::sendMessage(
                                                    $senderId,
                                                    // count($lich) > 0 ? $result : "Hôm nay bạn không có lịch!!!"
                                                    "`Hôm nay, tôi vẫn đang phát triển!!` :\">"
                                                );

                                                // $this->db->set("webhook-errors", \microtime(true) * 10000, $fbresp->getBody());
                                            } else {
                                                $fbresp = self::sendMessage(
                                                    $senderId,
                                                    "Bạn chưa đăng nhập, vui lòng sử dụng lệnh ``login`` để liên kết với tài khoản sinh viên."
                                                );

                                                // $this->db->set("webhook-errors", \microtime(true) * 10000, $fbresp->getBody());
                                            }
                                            break;
                                        case 'TOMORROW':
                                        case 'NGÀY MAI':
                                            if ($token) {
                                                $fbresp = self::sendMessage(
                                                    $senderId,
                                                    "Demo lịch ngày mai."
                                                );

                                                // $this->db->set("webhook-errors", \microtime(true) * 10000, $fbresp->getBody());
                                            } else {
                                                self::sendMessage(
                                                    $senderId,
                                                    "Bạn chưa đăng nhập, vui lòng sử dụng lệnh ``login`` để liên kết với tài khoản sinh viên."
                                                );
                                            }
                                            break;
                                        default:
                                            // code...
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
