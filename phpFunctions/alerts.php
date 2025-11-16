<!-- sweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php

if (isset($_SESSION['status']) && $_SESSION['status'] !== "") { ?>

    <script>
        console.log("swal ready");
        Swal.fire({
            title: '<?= $_SESSION['status'] ?>',
            text: '<?= $_SESSION['status_message'] ?>',
            icon: '<?= $_SESSION['status_icon'] ?>',
            confirmButtonText: 'Okay'
        })
    </script>

    <?php
    unset($_SESSION['status']);
}