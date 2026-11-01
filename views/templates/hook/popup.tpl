<div id="couponpopup" class="couponpopup" role="dialog" aria-modal="true" aria-labelledby="couponpopup-title" data-delay="{$couponpopup_delay|intval}" data-reappear-days="{$couponpopup_days|intval}" style="display:none;">
  <div class="couponpopup__box">
    <button type="button" class="couponpopup__close" aria-label="{l s='Close' mod='couponpopup'}">&times;</button>

    <div class="couponpopup__content">
      <h2 id="couponpopup-title" class="couponpopup__title">
        {l s='%s off!' sprintf=[$couponpopup_discount] mod='couponpopup'}
      </h2>

      <p class="couponpopup__text">
        {l s='Sign up and use the coupon %1$s to get %2$s off your purchase.' sprintf=[$couponpopup_code, $couponpopup_discount] mod='couponpopup'}
      </p>

      <a href="{$urls.pages.register|escape:'html':'UTF-8'}" class="couponpopup__button">
        {l s='Sign up' mod='couponpopup'}
      </a>

      <button type="button" class="couponpopup__decline">
        {l s='No, thanks' mod='couponpopup'}
      </button>
    </div>
  </div>
</div>
