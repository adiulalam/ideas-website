<?php
function generateAlert($message)
{
    $alert = "<style>

    /*Toast open/load animation*/
    .alert-toast {
        -webkit-animation: slide-in-right 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
        animation: slide-in-right 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
    }

    /*Toast close animation*/
    .alert-toast input:checked~* {
        -webkit-animation: fade-out-right 0.7s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
        animation: fade-out-right 0.7s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
    }

    /* -------------------------------------------------------------
	 * Animations generated using Animista * w: http://animista.net, 
	 * ---------------------------------------------------------- */

    @-webkit-keyframes slide-in-top {
        0% {
            -webkit-transform: translateY(-1000px);
            transform: translateY(-1000px);
            opacity: 0
        }

        100% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }
    }

    @keyframes slide-in-top {
        0% {
            -webkit-transform: translateY(-1000px);
            transform: translateY(-1000px);
            opacity: 0
        }

        100% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }
    }

    @-webkit-keyframes slide-out-top {
        0% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }

        100% {
            -webkit-transform: translateY(-1000px);
            transform: translateY(-1000px);
            opacity: 0
        }
    }

    @keyframes slide-out-top {
        0% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }

        100% {
            -webkit-transform: translateY(-1000px);
            transform: translateY(-1000px);
            opacity: 0
        }
    }

    @-webkit-keyframes slide-in-bottom {
        0% {
            -webkit-transform: translateY(1000px);
            transform: translateY(1000px);
            opacity: 0
        }

        100% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }
    }

    @keyframes slide-in-bottom {
        0% {
            -webkit-transform: translateY(1000px);
            transform: translateY(1000px);
            opacity: 0
        }

        100% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }
    }

    @-webkit-keyframes slide-out-bottom {
        0% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }

        100% {
            -webkit-transform: translateY(1000px);
            transform: translateY(1000px);
            opacity: 0
        }
    }

    @keyframes slide-out-bottom {
        0% {
            -webkit-transform: translateY(0);
            transform: translateY(0);
            opacity: 1
        }

        100% {
            -webkit-transform: translateY(1000px);
            transform: translateY(1000px);
            opacity: 0
        }
    }

    @-webkit-keyframes slide-in-right {
        0% {
            -webkit-transform: translateX(1000px);
            transform: translateX(1000px);
            opacity: 0
        }

        100% {
            -webkit-transform: translateX(0);
            transform: translateX(0);
            opacity: 1
        }
    }

    @keyframes slide-in-right {
        0% {
            -webkit-transform: translateX(1000px);
            transform: translateX(1000px);
            opacity: 0
        }

        100% {
            -webkit-transform: translateX(0);
            transform: translateX(0);
            opacity: 1
        }
    }

    @-webkit-keyframes fade-out-right {
        0% {
            -webkit-transform: translateX(0);
            transform: translateX(0);
            opacity: 1
        }

        100% {
            -webkit-transform: translateX(50px);
            transform: translateX(50px);
            opacity: 0
        }
    }

    @keyframes fade-out-right {
        0% {
            -webkit-transform: translateX(0);
            transform: translateX(0);
            opacity: 1
        }

        100% {
            -webkit-transform: translateX(50px);
            transform: translateX(50px);
            opacity: 0
        }
    }
    </style>    
    <div class='alert-toast fixed bottom-0 right-0 m-8 w-5/6 md:w-full max-w-sm'>
        <input type='checkbox' class='hidden' id='footertoast'>
        
        <div class'close flex cursor-pointer' title='close' for='footertoast'> 
        <label class='close cursor-pointer flex items-center justify-center w-full p-3 dark:bg-gray-800 dark:border-gray-700 h-24 rounded shadow-lg text-white' title='close' for='footertoast'>
            $message
        </label>
    </div>
    </div>";
    echo $alert;
}
