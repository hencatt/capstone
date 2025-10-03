<?php
require_once "sidebar.php";
function addDelay($location, $user, $userPosition)
{
    if ($location !== "account") {
        echo '
<script src="../scripts/loadingSim.js" async></script>
<div id="loading-bar"></div>
    <div id="loader">
        <div class="row everything">
            <div class="col sidebar">
                '; ?><?php sidebar("$location", $userPosition);
                        echo '</div>
        </div>
    </div>
';
                    }

                    if ($location === 'account') {
                        echo '
<script src="../scripts/loadingSim.js" defer></script>
<div id="loading-bar"></div>
    <div id="loader">

        <div class="row everything">
            <div class="col sidebar">
                '; ?><?php sidebar("$location", $userPosition);
                        echo '</div>
            <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">';
                        echo '
                <div class="row mt-5">
                    <div class="col d-flex justify-content-center">Please Wait...</div>
                </div>
            </div>
        </div>
    </div>
';
                    }
                }
