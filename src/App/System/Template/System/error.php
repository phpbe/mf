<!--{head}-->
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
<!--{/head}-->


<!--{body}-->
<div id="vue">
    <a-alert
            message="错误"
            description="<?php echo $this->get('message', ''); ?>"
            type="error"
    ></a-alert>

    <?php
    if (isset($this->redirectUrl) && isset($this->redirectTimeout) && $this->redirectTimeout > 0 )
    {
        ?>
        <p>
            <span id="redirect-timeout"><?php echo $this->redirectTimeout; ?></span>>秒后<a href="<?php echo $this->redirectUrl; ?>">跳转</a>
        </p>
        <?php
    }
    ?>
</div>


<!--{/body}-->