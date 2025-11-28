<style>
    header {
        display: flex;
        margin: auto;
        background-color: #c5c5cc;
        font-size: 10pt;
    }

    header .header_img {
        width: 500px;
        height: 128px;
    }

    header img {
        width: 100%;   
    }

    .article {
        display: flex;
        justify-items: center;
        align-items: center;
        gap: 8px;
    }

    .article_name {
        padding: 4px 6px;
        border: 1px solid ;
    }

    .seuil > p {
        line-height: 1rem;
    }
</style>
<header >
    <div class="header_img">
        @php $logo = public_path('images/small_header.webp'); @endphp
        @inlinedImage($logo)
    </div>

    <div>
        <div class="article ">
            CARDEX ARTICLE
            <span class="article_name">{{ $article->designation }}</span>
        </div>
        <div class="seuil">
            <p  class="maximum"><span>Maximum :</span> </p>
            <p><span class="">Minimum : :</span> </p>
        </div>
    </div>
    
</header>