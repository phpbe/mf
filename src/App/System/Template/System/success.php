<be-head>
<?php
if (isset($this->redirectUrl)) {

    $redirectTimeout = isset($this->redirectTimeout) ? $this->redirectTimeout : 0;
    if ($redirectTimeout > 0) {
        ?>
        <script>
            var iRedirectTimeout = <?php echo $redirectTimeout; ?>;
            setInterval(function () {
                iRedirectTimeout--;
                $("#redirect-timeout").html(iRedirectTimeout);
                if (iRedirectTimeout <= 0) {
                    window.location.href = "<?php echo $this->redirectUrl; ?>";
                }
            }, 1000);

        </script>
        <?php

    } else {
        ?>
        <script>
            $(document).ready(function () {
                window.location.href = "<?php echo $this->redirectUrl; ?>";
            });
        </script>
        <?php
    }

}
?>
</be-head>

<be-body>

    <div style="text-align: center; font-size: 48px; padding: 40px;">
        <i class="el-icon-success"></i>
    </div>

    <div style="text-align: center; font-size: 48px; padding: 40px;">
        <?php echo $this->message; ?>
    </div>

    <?php
    if (isset($this->redirectUrl) && isset($this->redirectTimeout) && $this->redirectTimeout > 0 )
    {
        ?>
        <div style="text-align: center; font-size: 48px; padding: 40px;">
            <span id="redirect-timeout"><?php echo $this->redirectTimeout; ?></span>>秒后跳转
        </div>
        <?php
    }
    ?>ccccc

</be-body>
