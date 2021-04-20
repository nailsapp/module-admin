        <!--    GLOBAL JS   -->
        <?php

        foreach(\Nails\Admin\Helper::getModals() as $oModal) {
            ?>
            <div class="modal<?=$oModal->open ? ' modal--open' : ''?>">
                <div class="modal__inner">
                    <div class="modal__close">&times;</div>
                    <div class="modal__title"><?=$oModal->title?></div>
                    <div class="modal__body"><?=$oModal->body?></div>
                </div>
            </div>
            <?php
        }

        $oAsset = \Nails\Factory::service('Asset');
        $oAsset->output('JS');
        $oAsset->output('JS-INLINE-FOOTER');

        ?>
    </body>
</html>
