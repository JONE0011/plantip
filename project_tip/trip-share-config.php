<?php
/**
 * Shared-trip token helper.
 * เปลี่ยนค่านี้เป็นข้อความสุ่มยาว ๆ ของโปรเจกต์คุณก่อนใช้งานจริง
 */
const TRIP_SHARE_SECRET = 'TAK_EXPLORE_TRIP_SHARE_2026_CHANGE_THIS_SECRET_8f3d91';

function create_trip_share_token(int $id_account): string {
    $payload = (string)$id_account;
    $signature = hash_hmac('sha256', $payload, TRIP_SHARE_SECRET);
    return rtrim(strtr(base64_encode($payload . '|' . $signature), '+/', '-_'), '=');
}

function decode_trip_share_token(string $token): ?int {
    $raw = base64_decode(strtr($token, '-_', '+/'), true);
    if($raw === false) return null;

    $parts = explode('|', $raw, 2);
    if(count($parts) !== 2 || !ctype_digit($parts[0])) return null;

    $id_account = (int)$parts[0];
    $expected = hash_hmac('sha256', (string)$id_account, TRIP_SHARE_SECRET);

    if(!hash_equals($expected, $parts[1])) return null;
    return $id_account;
}
?>
