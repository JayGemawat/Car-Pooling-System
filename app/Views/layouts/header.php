<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>JaanaHai — Car Pool</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- styles -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/common.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/datetimepicker.css">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3b82f6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="JaanaHai">

    <!-- Map utilities -->
    <script src="/js/map.js"></script>

    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(console.warn);
    }
    </script>
</head>
<body>
<div id="wrap">
  <div class="container-fluid">
    <header class="row-fluid">
      <div class="span2">
        <img src="/img/logo.jpg" width="120" class="img-polaroid" alt="JaanaHai">
      </div>
      <div class="span10">
        <div class="row-fluid">
          <div class="span10">
            <h3 align="center">Car Pooling</h3>
          </div>
          <div class="span2">
            <a class="btn btn-info" href="/" role="button">Home</a>
          </div>
        </div>
      </div>
    </header>
  </div>
