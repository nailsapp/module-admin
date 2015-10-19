<div class="group-admin group-admin-styleguide">
    <p class="alert alert-info">
     @todo: Complete a style guide for admin showing all the components and how the current admin stylesheet/theme renders them.
    </p>
    <hr />

    <!-- Typography -->
    <section class="typography">
        <div class="title">
            Typography
        </div>
        <div class="description">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua.
        </div>
        <div class="body">
            <h1>This is a &lt;h1&gt; heading</h1>
            <h2>This is a &lt;h2&gt; heading</h2>
            <h3>This is a &lt;h3&gt; heading</h3>
            <h4>This is a &lt;h4&gt; heading</h4>
            <h5>This is a &lt;h5&gt; heading</h5>
            <h6>This is a &lt;h6&gt; heading</h6>
            <p>
                This is some body text. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </p>
            <p>
                <small>This is some small text</small>
            </p>
            <p>
                <strong>This is some strong text</strong>
            </p>
            <p>
                <em>This is some italic text</em>
            </p>
        </div>
    </section>
    <!-- /Typography -->

    <!-- Colours -->
    <section class="alerts">
        <div class="title">
            Colours
        </div>
        <div class="body">
        </div>
    </section>
    <!-- /Colours -->

    <!-- Buttons -->
    <section class="alerts">
        <div class="title">
            Buttons
        </div>
        <div class="body">
            <?php

                $aSizes = array('btn-xs', 'btn-sm', '', 'btn-lg');
                $aTypes = array(
                    'btn-default',
                    'btn-primary',
                    'btn-secondary',
                    'btn-success',
                    'btn-danger',
                    'btn-info',
                    'btn-warning',
                    'btn-link'
                );

                foreach ($aSizes as $sSize) {
                    foreach ($aTypes as $sType) {
                        ?>
                        <button class="btn <?=$sType?> <?=$sSize?>">
                            Button
                        </button>
                        <?php
                    }
                    echo '</p>';
                }

            ?>
        </div>
    </section>
    <!-- /Buttons -->

    <!-- Alerts -->
    <section class="alerts">
        <div class="title">
            Alerts
        </div>
        <div class="body">
            <p class="alert alert-success">
                This is a <strong>success</strong> alert.
            </p>
            <p class="alert alert-danger">
                This is a <strong>danger</strong> alert.
            </p>
            <p class="alert alert-info">
                This is an <strong>info</strong> alert.
            </p>
            <p class="alert alert-warning">
                This is a <strong>warning</strong> alert.
            </p>
        </div>
    </section>
    <!-- /Alerts -->

    <!-- Tables -->
    <section class="alerts">
        <div class="title">
            Tables
        </div>
        <div class="body">
            <table>
                <thead>
                    <tr>
                        <th>Column 1</th>
                        <th>Column 2</th>
                        <th>Column 3</th>
                        <th>Column 4</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Column 1</td>
                        <td>Column 2</td>
                        <td>Column 3</td>
                        <td>Column 4</td>
                    </tr>
                    <tr>
                        <td>Column 1</td>
                        <td>Column 2</td>
                        <td>Column 3</td>
                        <td>Column 4</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    <!-- /Tables -->

    <!-- Fieldsets -->
    <section class="fieldsets">
        <div class="title">
            Fieldsets
        </div>
        <div class="body">
            <p>
                An example of a normal, top level fieldset.
            </p>
            <fieldset>
                <legend>I Am Legend</legend>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua.
                </p>
                <table>
                    <thead>
                        <tr>
                            <th>Column 1</th>
                            <th>Column 2</th>
                            <th>Column 3</th>
                            <th>Column 4</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Column 1</td>
                            <td>Column 2</td>
                            <td>Column 3</td>
                            <td>Column 4</td>
                        </tr>
                        <tr>
                            <td>Column 1</td>
                            <td>Column 2</td>
                            <td>Column 3</td>
                            <td>Column 4</td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua.
                </p>
            </fieldset>
            <p>
                An example of a nested fieldset.
            </p>
            <fieldset>
                <legend>I Am Legend</legend>
                <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua.
                </p>
                <fieldset>
                    <legend>I Am Legend</legend>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                    <table>
                        <thead>
                            <tr>
                                <th>Column 1</th>
                                <th>Column 2</th>
                                <th>Column 3</th>
                                <th>Column 4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Column 1</td>
                                <td>Column 2</td>
                                <td>Column 3</td>
                                <td>Column 4</td>
                            </tr>
                            <tr>
                                <td>Column 1</td>
                                <td>Column 2</td>
                                <td>Column 3</td>
                                <td>Column 4</td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                        tempor incididunt ut labore et dolore magna aliqua.
                    </p>
                </fieldset>
            </fieldset>
        </div>
    </section>
    <!-- /Alerts -->
</div>