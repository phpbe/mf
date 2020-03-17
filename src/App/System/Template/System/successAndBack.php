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
                    document.getElementById("from-history").submit();
                }
            }, 1000);

        </script>
        <?php

    } else {
        ?>
        <script>
            $(document).ready(function () {
                document.getElementById("from-history").submit();
            });
        </script>
        <?php
    }
}
?>
<!--{/head}-->



<!--{body}-->
<div class="theme-box-container">
    <div class="theme-box">
        <div class="theme-box-title">操作成功</div>
        <div class="theme-box-body">
            <p>
                <?php echo $this->message; ?>
            </p>

            <?php
            if (isset($this->redirectTimeout) && $this->redirectTimeout > 0 )
            {
                ?>
                <p>
                    <span id="redirect-timeout"><?php echo $this->redirectTimeout; ?></span>>秒后跳转
                </p>
                <?php
            }
            ?>

            <form action="<?php echo $this->historyUrl; ?>" id="from-history" method="post">
                <?php
                if (is_array($this->historyPost) && count($this->historyPost) > 0) {
                    foreach ($this->historyPost as $key => $val) {
                        echo '<input type="hidden" name="' . $key . '" value="' . $val . '"/>';
                    }
                }
                ?>
            </form>

        </div>
    </div>
</div>
<!--{/body}-->
