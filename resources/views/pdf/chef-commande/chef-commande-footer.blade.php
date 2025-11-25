<style>
    footer {
        position: relative;
        width: 100%;
        font-size: 10pt;
    }

    footer .img {
        width: 550px;
        /* height: 128px; */
        margin-inline: auto;
    }

    footer img {
        max-width: 100%;
    }

    footer .pageNum {
        position: absolute;
        bottom: 0px;
        left: 15px;
        
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        /* approx 8 units */
        text-align: center;
        margin-bottom: 36px;
        gap: 35px;
        padding: 0 50px;
    }

    .role {
        font-weight: bold;
        padding-bottom: 25px;
        border-bottom: 1px dashed #000;
    }
</style>
<footer>
    <!-- <div class="grid">
        <div class="role">Le magasinier</div>
            <div class="role">L'économe</div>
            <div class="role">Le directeur</div>
        </div>
    </div> -->

    <div class="img">
        @php $logo = public_path('images/small_footer.webp'); @endphp
        @inlinedImage($logo)
    </div>
    <!-- <h1>Hello footer test</h1> -->

    <div class="pageNum">
        <span>@pageNumber/@totalPages</span>
    </div>
</footer>