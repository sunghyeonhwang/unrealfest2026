<?php
/* Unreal Fest Seoul 2026 — English legal/policy page (legal-en.php)
 * Terms of Service · Cancellation & Refund Policy · Privacy Policy · Company/Contact.
 * MoR(Dodo Payments) 도메인 심사 + 해외 등록자용. 디지털 등록·현장 수령(무배송) 프레이밍.
 * Standalone. 한글 약관(_legal_modal.php)과 정합. noindex 아님(심사 시 공개 접근 필요).
 */
require __DIR__ . '/_ticket_init.php';
$anchor = isset($_GET['s']) ? preg_replace('/[^a-z]/','',$_GET['s']) : '';
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Legal &amp; Policies — Unreal Fest Seoul 2026</title>
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>
  body{font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
  .doc{max-width:820px;margin:0 auto;padding:120px 24px 100px}
  .doc h1{font-size:28px;font-weight:800;margin:0 0 8px}
  .doc h2{font-size:19px;font-weight:800;color:#fff;margin:44px 0 12px;padding-top:10px;border-top:1px solid #27272a}
  .doc h3{font-size:15px;font-weight:700;color:#e4e4e7;margin:22px 0 8px}
  .doc p,.doc li{color:#a1a1aa;font-size:14px;line-height:1.75}
  .doc ul{margin:8px 0 8px 18px;list-style:disc}
  .doc li{margin:4px 0}
  .doc a{color:#00C1D5;text-decoration:underline}
  .doc table{width:100%;border-collapse:collapse;margin:10px 0;font-size:13px}
  .doc th,.doc td{border:1px solid #27272a;padding:8px 10px;text-align:left;color:#a1a1aa}
  .doc th{background:#111115;color:#e4e4e7}
  .note{background:#0e1416;border:1px solid #14343b;border-radius:8px;padding:14px 16px;margin:14px 0}
  .toc{display:flex;gap:16px;flex-wrap:wrap;margin:18px 0 8px;font-size:13px}
  .updated{color:#71717a;font-size:13px}
</style>
<?php include __DIR__ . '/_favicon.php'; ?>
</head>
<body class="bg-[#09090b] text-white">

<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="ticket-en.php" class="text-sm text-[#a1a1aa] hover:text-white">Registration →</a>
  </div>
</header>

<div class="doc">
  <h1>Legal &amp; Policies</h1>
  <p class="updated">Unreal Fest Seoul 2026 · Last updated: July 2026</p>

  <div class="toc">
    <a href="#about">Product &amp; Service</a>
    <a href="#terms">Terms of Service</a>
    <a href="#refund">Cancellation &amp; Refund</a>
    <a href="#privacy">Privacy Policy</a>
    <a href="#contact">Company &amp; Contact</a>
  </div>

  <div class="note">
    <p style="color:#e4e4e7;margin:0"><strong>What you are purchasing.</strong> Registration on this page grants <strong>admission to Unreal Fest Seoul 2026</strong>, an in-person developer conference held on <strong>August 20–21, 2026</strong> at COEX, Seoul, Republic of Korea. This is a <strong>digital registration/admission service</strong>. Any merchandise included with certain ticket types (event T-shirt, limited-edition goods) is provided for <strong>on-site pickup at the venue only and is not shipped</strong>.</p>
  </div>
  <p style="font-size:13px;color:#71717a">Payments on this page are processed by <strong>Dodo Payments</strong>, acting as the Merchant of Record (reseller) for this transaction. Prices are charged in <strong>USD</strong>. The Merchant of Record name may appear on your card statement.</p>

  <h2 id="about">1. Product &amp; Service Description</h2>
  <ul>
    <li><strong>Event:</strong> Unreal Fest Seoul 2026 — a two-day, in-person conference for developers and creators.</li>
    <li><strong>Dates:</strong> August 20 (Thu) – 21 (Fri), 2026. <strong>Venue:</strong> COEX, Seoul, Republic of Korea.</li>
    <li><strong>Ticket types:</strong> All-Days Pass (Aug 20–21), 1-Day Pass (Aug 20), 1-Day Pass (Aug 21).</li>
    <li><strong>What is delivered:</strong> An electronic registration confirmation and a QR admission code delivered by email immediately after payment is confirmed. No physical item is shipped.</li>
    <li><strong>On-site benefits:</strong> Session access, and (for eligible tickets) an event T-shirt and limited-edition goods handed out at the venue during the event. These are on-site benefits, not shipped products.</li>
  </ul>

  <h2 id="terms">2. Terms of Service</h2>
  <h3>2.1 Registration</h3>
  <ul>
    <li>You must provide accurate registration information (name, email, phone). Your QR admission code is sent to the email you provide, so please ensure it is correct.</li>
    <li>Each paid registration admits one (1) person. Admission codes are personal and may not be resold or transferred without the organizer's consent.</li>
    <li>The organizer may refuse or cancel a registration in cases of fraud, chargeback abuse, or violation of these terms or venue rules.</li>
  </ul>
  <h3>2.2 Conduct &amp; Admission</h3>
  <ul>
    <li>Attendees must follow venue safety rules and staff instructions. The organizer may deny entry or remove attendees for unsafe or disruptive behavior.</li>
    <li>The event may be photographed or recorded; by attending you consent to such recording for event and promotional purposes.</li>
  </ul>
  <h3>2.3 Changes to the Event</h3>
  <ul>
    <li>In the event of force majeure (natural disaster, epidemic, government order, venue or speaker circumstances, safety needs, etc.), the organizer may change the schedule, venue, format, sessions, speakers, or benefits, or cancel the event. Material adverse changes will be communicated via the registration page, email, or SMS.</li>
  </ul>

  <h2 id="refund">3. Cancellation &amp; Refund Policy</h2>
  <ul>
    <li><strong>Before the event begins:</strong> You may request cancellation by email. You will be refunded the ticket price, less any non-refundable costs actually incurred (payment processing fees, currency/remittance fees, and the cost of any goods or services already provided).</li>
    <li><strong>After the event begins or after you have entered the venue:</strong> Refunds are, in principle, not available, except where consumer-protection or e-commerce law requires otherwise.</li>
    <li><strong>Organizer cancellation:</strong> If the organizer cancels the event, paid registrations are refunded in full. If the event is rescheduled, your registration remains valid for the new date, or you may request a refund.</li>
    <li><strong>How to request:</strong> Email <a href="mailto:info@epiclounge.co.kr">info@epiclounge.co.kr</a> with your registration name and the email used at checkout. Refunds are returned to the original payment method and are processed through Dodo Payments; the time to appear on your statement depends on your card issuer.</li>
  </ul>

  <h2 id="privacy">4. Privacy Policy (Summary)</h2>
  <p>The organizer, GRIFF Inc., collects and processes personal data to operate event registration and admission.</p>
  <table>
    <tr><th>Data collected</th><th>Purpose</th><th>Retention</th></tr>
    <tr><td>Name, email, phone, company/role (optional), T-shirt size</td><td>Registration, admission (QR), event communications, on-site operations</td><td>Until event completion + statutory periods</td></tr>
    <tr><td>Payment identifiers (method, amount, time, approval/transaction ID)</td><td>Payment, refund, accounting, fraud prevention</td><td>As required by applicable law</td></tr>
  </table>
  <ul>
    <li><strong>Payment processing:</strong> Card details are collected and processed by <strong>Dodo Payments</strong> (Merchant of Record) and its acquiring partners. GRIFF Inc. does not store your full card number.</li>
    <li><strong>Your rights:</strong> You may request access, correction, or deletion of your data by emailing <a href="mailto:info@epiclounge.co.kr">info@epiclounge.co.kr</a>.</li>
    <li>Data is not sold. It is shared only with service providers necessary to deliver the event (payment processor, email/SMS delivery, venue operations) and as required by law.</li>
  </ul>

  <h2 id="contact">5. Company &amp; Contact</h2>
  <table>
    <tr><td style="width:38%">Legal entity</td><td>GRIFF Inc. (주식회사 그리프)</td></tr>
    <tr><td>Representative</td><td>Sung-hyun Hwang</td></tr>
    <tr><td>Business Registration No.</td><td>859-88-00263</td></tr>
    <tr><td>Mail-order Sales Registration</td><td>2018-Seoul Songpa-0571</td></tr>
    <tr><td>Address</td><td>#1102-1103, SK V1 CENTER2, 31 Gwangnaru-ro 8-gil, Seongdong-gu, Seoul, Republic of Korea</td></tr>
    <tr><td>Customer support</td><td>+82-2-326-3701</td></tr>
    <tr><td>Email</td><td><a href="mailto:info@epiclounge.co.kr">info@epiclounge.co.kr</a></td></tr>
  </table>
  <p style="margin-top:18px"><a href="ticket-en.php">← Back to registration</a></p>
</div>

<?php if ($anchor): ?><script>location.hash = '#' + <?= json_encode($anchor) ?>;</script><?php endif; ?>
</body>
</html>
