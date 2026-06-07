{{-- Global scripts (deferred). Bootstrap/jQuery removed — not used in main design. --}}
{{-- jQuery lives in __app.blade.php only, where profile-setup pages need it. --}}

{{-- ═══════════════════════════════════════════════════════════════════
     MARKETING ANALYTICS — all IDs pulled from .env / Railway variables
     Set these on Railway:
       GOOGLE_ANALYTICS_ID   = G-XXXXXXXXXX
       FACEBOOK_PIXEL_ID     = 123456789012345
       MICROSOFT_CLARITY_ID  = abcdefghij
     ═══════════════════════════════════════════════════════════════════ --}}

{{-- ── Google Analytics 4 ─────────────────────────────────────────── --}}
@if(config('services.analytics.ga4_id'))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.analytics.ga4_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config('services.analytics.ga4_id') }}', {
    page_title: document.title,
    page_location: window.location.href,
    // Don't send PII — strip email/phone from page paths
    anonymize_ip: true
  });

  // Track key events automatically
  document.addEventListener('DOMContentLoaded', function() {
    // Track lead form submissions
    document.querySelectorAll('form[data-track]').forEach(function(form) {
      form.addEventListener('submit', function() {
        gtag('event', form.dataset.track, { event_category: 'engagement' });
      });
    });
    // Track CTA button clicks
    document.querySelectorAll('[data-ga-event]').forEach(function(el) {
      el.addEventListener('click', function() {
        gtag('event', el.dataset.gaEvent, {
          event_category: el.dataset.gaCategory || 'click',
          event_label: el.dataset.gaLabel || el.innerText
        });
      });
    });
  });
</script>
@endif

{{-- ── Facebook Pixel ──────────────────────────────────────────────── --}}
@if(config('services.analytics.fb_pixel_id'))
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ config('services.analytics.fb_pixel_id') }}');
fbq('track', 'PageView');

// Track lead events — call fbq('track','Lead') on form submit
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('form[data-track-lead]').forEach(function(form) {
    form.addEventListener('submit', function() { fbq('track', 'Lead'); });
  });
  document.querySelectorAll('form[data-track-register]').forEach(function(form) {
    form.addEventListener('submit', function() { fbq('track', 'CompleteRegistration'); });
  });
});
</script>
<noscript>
  <img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ config('services.analytics.fb_pixel_id') }}&ev=PageView&noscript=1"/>
</noscript>
@endif

{{-- ── Microsoft Clarity (heatmaps + session recordings) ─────────── --}}
@if(config('services.analytics.clarity_id'))
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "{{ config('services.analytics.clarity_id') }}");
</script>
@endif
