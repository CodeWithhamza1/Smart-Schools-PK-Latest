<html>
<head>
    <title>Download Registration Form</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <style type="text/css">
        body { direction: rtl; }
    .underline{
        text-decoration: underline;
    }
        @media print {
            .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
                float: left;
            }
            .col-sm-12 {
                width: 100%;
            }
            .col-sm-11 {
                width: 91.66666667%;
            }
            .col-sm-10 {
                width: 83.33333333%;
            }
            .col-sm-9 {
                width: 75%;
            }
            .col-sm-8 {
                width: 66.66666667%;
            }
            .col-sm-7 {
                width: 58.33333333%;
            }
            .col-sm-6 {
                width: 50%;
            }
            .col-sm-5 {
                width: 41.66666667%;
            }
            .col-sm-4 {
                width: 33.33333333%;
            }
            .col-sm-3 {
                width: 25%;
            }
            .col-sm-2 {
                width: 16.66666667%;
            }
            .col-sm-1 {
                width: 8.33333333%;
            }
        }

    </style>
</head>
<body style=" font-weight: bold;" >
<div class="container"style="border: 2px solid black;padding: 2px; font-size: 18px; width: 21cm;
  min-height: 29.7cm;">
    <div style="border: 2px solid black;padding: 20px;width: 21cm;
  min-height: 29.7cm;">
<!--        <span> 1</span>-->
    <div class="">
        <div class="col-md-12">
            <h1 style="text-align: center;"class="underline">حلف نامہ</h1>

        </div>

    </div>
        <br>
        <br>
    <div class="row col-sm-12">
        <div class="col-md-12">
            <p>تاریخ:<span class="underline"> <?php echo $date?></span>  </p>
        </div>
    </div>
        <br>
        <br>
        <br>

        <div class="row col-sm-12">
        <div class="col-sm-6">
            <p>قبیلہ:_____________________<!--<span class="underline">date</span>-->  </p>
        </div>
        <div class="col-sm-6">
            <p>میں مسّمی/مسماۃ:_____________________<span class="underline"></span>  </p>
        </div>

    </div>
        <br>
        <br>


        <div class="row col-sm-12">
        <div class="col-sm-6">
            <p>آمدنی:____________________<!--<span class="underline"></span>-->  </p>
        </div>
        <div class="col-sm-6 ">
            <p>والد/سربراہ کا پیشہ :<span class="underline"><?php echo $student['father_occupation']; ?></span>  </p>
        </div>

    </div>
        <br>
        <br>

        <div class="row col-sm-12">
        <div class="col-sm-6">
            <p>موبائل:<span class="underline"><?php echo $student['guardian_phone']; ?></span>  </p>
        </div>
        <div class="col-sm-6 ">
            <p>شناختی کارڈ #:_____________________<!--<span class="underline">id</span>-->  </p>
        </div>

    </div>
        <br>
        <br>

        <div class="row col-sm-12">
        <div class="col-sm-12">
            <p>پتہ:<span class="underline" style=""><?php echo $student['guardian_address']; ?></span>  </p>
        </div>


    </div>
        <br>
        <br>

        <div class="row col-sm-12">
        <div class="col-sm-12">
            <p>میں اللہ تعالیٰ ، رسول ﷺ  اور آئمہ طاہرین ؑ کو حاضر و ناظر مان کر حلفاً اقرار کرتا / کرتی ہوں کہ میرے مالی حالات کمزور ہیں  اور میں زکوٰۃ، خمس یا مال خدا کا / کی مُستحق ہوں ـ</p>
        </div>
    </div>
        <br>        <br>


        <div class="row col-sm-12">
        <div class="col-sm-6">
            <p>دستخط درخواست دہندہ:__________________  </p>
        </div>
    </div>
        <br>
        <br>
        <br>
    <div class="row col-sm-12">
        <div class="col-sm-12">
            <p> لہذا میرے بچے / بچی کو مندرجہ ذیل رعایت دی  جائے :______________________________<!--<span class="underline"></span>-->  </p>
        </div>
    </div>
        <br>
        <br><br>

        <div class="row col-sm-12" >
        <div class="col-sm-5">
            <p>امتحانی کارکردگی:_____________<!--<span class="underline">num</span> --> </p>
        </div>
        <div class="col-sm-3 ">
            <p>جماعت :<span class="underline"><?php echo $student["class"]." (".$student["section"].")"; ?></span>  </p>
        </div>

        <div class="col-sm-4 ">
            <p>نام طالب علم :<span class="underline"><?php echo $student["firstname"]." ".$student["lastname"] ?></span>  </p>
        </div>

    </div>
        <br>
        <br>

        <div class="row col-sm-12">
        <div class="col-sm-5">
            <p>سکول حاضری :______________<!--<span class="underline">num</span> --> </p>
        </div>
        <div class="col-sm-3 ">
            <p>بیلنس فیس :<span class="underline"><?php echo $student_pending_fee; ?></span>  </p>
        </div><div class="col-sm-4 ">
            <p>موجودہ فیس :<span class="underline"><?php echo $totalFees; ?></span>  </p>
        </div>

    </div>
        <br>
        <br><br>
        <br>

        <div class="row col-sm-12">
        <div class="col-sm-12">
            <p class="underline">نوٹ: منظور شدہ/دستخط شدہ درخواست 3 یوم تک کار آمد ہوگی۔ اس لیے 3 دن کے اندر واجبات جمع کروانا لازمی ہے۔ </p>
        </div>

        </div>
        <br>
        <br> <br>
        <br>

        <div class="row col-sm-12">
        <div class="col-sm-12">
            <p class="underline">ہدایات منجانب  کنونئیر/ پرنسپل </p>
        </div>

        </div>
        <div class="row col-sm-12">
        <div class="col-sm-12">
            <p class="">______________________________________________________________________</p>
            <br>

            <p class="">______________________________________________________________________</p>
        </div>


        </div>
        <br>
        <br>
        <br>


        <div class="row col-sm-12">
            <br>
            <br>
            <br>
        <div class="col-sm-6">
            <p class="">دستخط پرنسپل___________________ </p>
        </div><div class="col-sm-6">
            <p class="">دستخط کنونئیر___________________ </p>
        </div>

        </div>
    </div>
</div>
</body>
</html>
