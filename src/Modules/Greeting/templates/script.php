<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"
        integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.28/moment-timezone-with-data.min.js"
        integrity="sha256-IWYg4uIC8/erItNXYvLtyYHioRi2zT1TFva8qaAU/ww=" crossorigin="anonymous"></script>
<script>
    const hours = document.getElementById('greeting-hours');
    const minutes = document.getElementById('greeting-minutes');

    function greetingUpdate() {
        const now = moment().tz("<?= $tz ?>");
        hours.innerHTML = now.format("HH");
        minutes.innerHTML = now.format("mm");
    }

    setInterval(greetingUpdate, 1000);
</script>
