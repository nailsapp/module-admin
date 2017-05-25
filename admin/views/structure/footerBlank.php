        <!--    GLOBAL JS   -->
        <?php

        $oAsset = \Nails\Factory::service('Asset');
        $oAsset->output('JS');
        $oAsset->output('JS-INLINE-FOOTER');

        ?>
    </body>
</html>