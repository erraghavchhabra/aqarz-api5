<!DOCTYPE html>
<html lang="en">
<head>
        <meta name="description" content=" عقارز ">

    <meta charset="utf-8" />
<script>
function getMobileOperatingSystem() {
  var userAgent = navigator.userAgent || navigator.vendor || window.opera;

    if (/android/i.test(userAgent)) {
        return "Android";
    }

    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
        return "iOS";
    }

    return "unknown";
}</script>

<script>
function DetectAndServe(){

if (getMobileOperatingSystem() == "Android") {
    window.location.href = "https://play.google.com/store/apps/details?id=sa.aqarz";
    }
if (getMobileOperatingSystem() == "iOS") {
    window.location.href = "https://apps.apple.com/us/app/%D8%B9%D9%82%D8%A7%D8%B1%D8%B2/id1534362822";
    }

if (getMobileOperatingSystem() == "unknown") {
  window.location.href = "https://aqarz.sa/";}
};
</script>
</head>
<body onload="DetectAndServe()">
</body>
</html>
