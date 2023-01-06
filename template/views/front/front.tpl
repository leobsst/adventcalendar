{extends file='page.tpl'}

{if $customer.is_logged}
    {block name="page_content"}
        {if $month == 12}
            {if $today|in_array:$calendar}
                <section class="calendar-content">
                    <h1>Calendrier de l'avent</h1>
                    <h3>Obtenez votre promotion quotidienne</h3>
                    
                    <div class="calendar-form">
                        <form method="POST" class="calendar-day">
                            {foreach from=$calendar item=day}
                                {if $today == $day}
                                    <input type="submit" name="submit" class="day btn btn-danger m-1 p-1" value="{$day}" />
                                {else}
                                    <span class="day m-1 btn btn-secondary p-1">{$day}</span>
                                {/if}
                            {/foreach}
                        </form>
                    </div>
                    
                    
                    {if $code !== null && $promo !== null}
                        <br><h4>Votre code promo est {$code} pour {$promo}% de réduction</h4>
                    {/if}

                    </form>
                    {if $error !== null}
                        <br><div><h4 class="btn btn-danger error">{$error}</h4></div>
                    {/if}
                </section>
            {else}
                <section class="calendar-content">
                    <h1>Calendrier de l'avent terminé !</h1>
                    <h3>Revenez l'année prochaine 😀</h3>
                </section>
            {/if}
        {else}
            <section class="calendar-content">
                <h1>Calendrier de l'avent terminé !</h1>
                <h3>Revenez l'année prochaine 😀</h3>
            </section>
        {/if}
    {/block}
{else}
    {block name="page_content"}
        <section class="calendar-content">
            <h1>Veuillez d'abord vous connecter</h1>
            <a class="btn btn-primary" href="/index.php?controller=authentication">CONNEXION</a>
        </section>
    {/block}
{/if}