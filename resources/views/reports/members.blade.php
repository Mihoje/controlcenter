@extends('layouts.app')

@section('title', 'Members')

@section('content')
<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col me-2">
                <div class="fs-sm fw-bold text-uppercase text-gray-600 mb-1">Users in CC</div>
                <div class="h5 mb-0 fw-bold text-gray-800">{{ $cardStats["totalUsers"] }} users</div>
                </div>
                <div class="col-auto">
                <i class="fa-solid fa-globe fa-2x text-gray-300"></i>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col me-2">
                <div class="fs-sm fw-bold text-uppercase text-gray-600 mb-1">Users in subdivision</div>
                <div class="h5 mb-0 fw-bold text-gray-800">{{ $cardStats["inSubdivision"] }} users</div>
                </div>
                <div class="col-auto">
                <i class="fa-solid fa-house-user fa-2x text-gray-300"></i>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col me-2">
                <div class="fs-sm fw-bold text-uppercase text-gray-600 mb-1">Active ATC</div>
                <div class="h5 mb-0 fw-bold text-gray-800">{{ $cardStats["activeAtc"] }} users</div>
                </div>
                <div class="col-auto">
                <i class="fa-solid fa-tower-cell fa-2x text-gray-300"></i>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col me-2">
                <div class="fs-sm fw-bold text-uppercase text-gray-600 mb-1">Visiting users</div>
                <div class="h5 mb-0 fw-bold text-gray-800">{{ $cardStats["visiting"] }} users</div>
                </div>
                <div class="col-auto">
                <i class="fa-solid fa-door-open fa-2x text-gray-300"></i>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-6 col-md-12 mb-12 d-block">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    vACC users by rating
                </h6>
            </div>
            <div class="card-body d-flex align-content-center justify-content-center" style="position: relative; max-height: 60vh;">
                <canvas id="ratingsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-12 mb-12 d-block">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Active ATC by rating
                </h6>
            </div>
            <div class="card-body d-flex align-content-center justify-content-center" style="position: relative; max-height: 60vh;">
                <canvas id="atcChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 mb-12">
        <div class="card shadow mb4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Statistics by date
                </h6>
            </div>
            <div class="card-body" style="position: relative; max-height: 60vh;">
                <canvas id="statsChart"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
@vite('resources/js/chart.js')

<script>
    function ucFirst(string) {
        if (string) {
            string = string.replaceAll('_', ' ');
            return String(string)[0].toUpperCase() + String(string).substring(1)
        }
    }
    const max = (e) => {
        return e.reduce((prev, current) => (prev && prev.y > current.y) ? prev : current);
    }

    const sum = (e) => {
        return e.reduce((a, b) => a + b, 0);
    }

    document.addEventListener("DOMContentLoaded", function () {
        @php
            if(!isset($ratingUsers)){
                $ratingUsers = [];
            }

            if(!isset($ratingActiveAtc)){
                $ratingActiveAtc = [];
            }

            if(!isset($statsHistory)){
                $statsHistory = [];
            }
        @endphp
        const ratingsData = {!! json_encode($ratingUsers) !!};
        const activeAtcRatingsData = {!! json_encode($ratingActiveAtc) !!}

        const memberStatistics = {!! json_encode($statsHistory) !!}

        function getRatingColor(rating){

            switch(rating){
                case "INA":
                    return "#c7c7c7";
                case "SUS":
                    return "#8c0000";
                case "OBS":
                    return "#707070";
                case "S1":
                    return "#5ec5eb";
                case "S2":
                    return "#0d6adb";
                case "S3":
                    return "#ffe642";
                case "C1":
                    return "#f5650c";
                case "C3":
                    return "#ff863b";
                case "I1":
                    return "#ff3bf5";
                case "I3":
                    return "#c70060";
                case "SUP":
                    return "#ab00a2";
                case "ADM":
                    return "#00c7a6";
                default:00
                    return "green";
            }

        }

        const hideRatings = ["INA","OBS","SUS"];

        var hiddenIndexes = {};

        hideRatings.forEach(r => {
            hiddenIndexes[Object.keys(ratingsData).indexOf(r)] = true;
        });

        const ratingsCanvas = document.getElementById("ratingsChart").getContext('2d');
        const activeAtcRatingCanvas = document.getElementById("atcChart").getContext('2d');
        const statsCanvas = document.getElementById("statsChart").getContext('2d');

        const ratingDataChart = new Chart(ratingsCanvas, {
            type: 'pie',
            data: {
                datasets: [{
                    data: Object.values(ratingsData)
                }],
                labels: Object.keys(ratingsData)
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip:{
                        callbacks: {
                            label: function(c){
                                const s = sum(Object.values(ratingsData));
                                return [`${c.formattedValue} user${c.formattedValue!=1?'s':''}`, `${ Math.round((c.formattedValue / s) * 10000) / 100 }%`];
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                },
                elements: {
                    arc: {
                        backgroundColor: c => { return getRatingColor(Object.keys(ratingsData)[c.dataIndex]) }
                    }
                }
            },
        });

        ratingDataChart._hiddenIndices = hiddenIndexes;
        ratingDataChart.update();

        const activeAtcChart = new Chart(activeAtcRatingCanvas, {
            type: 'pie',
            data: {
                datasets: [{
                    data: Object.values(activeAtcRatingsData)
                }],
                labels: Object.keys(activeAtcRatingsData)
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip:{
                        callbacks: {
                            label: function(c){
                                const s = sum(Object.values(activeAtcRatingsData));
                                return [`${c.formattedValue} controller${c.formattedValue!=1?'s':''}`, `${ Math.round((c.formattedValue / s) * 10000) / 100 }%`];
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                },
                elements: {
                    arc: {
                        backgroundColor: c => { return getRatingColor(Object.keys(activeAtcRatingsData)[c.dataIndex]) }
                    }
                }
            },
        });

        const ds = [];

        Object.keys(memberStatistics).forEach(e => {
            ds.push({
                label: ucFirst(e),
                data: memberStatistics[e],
                yAxisID: max(memberStatistics[e]).y > 250 ? 'y' : 'y1',
            });
        });

        const statsChart = new Chart(statsCanvas, {
            type: 'line',
            data:{
                datasets: ds,
            },
            options:{
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                    }
                }
            }
        });
    });

</script>
@endsection
