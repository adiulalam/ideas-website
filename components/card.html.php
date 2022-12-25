<?php
function mutationCheck($IdeaID, $totalIdeas)
{
    require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

    $authorID = $_SESSION['aid'];

    if (!userIsLoggedIn() || !$authorID || !$totalIdeas || !$IdeaID) {
        return false;
    }

    if ((in_array($IdeaID, $totalIdeas)) ||
        ((in_array("Content Editor", $_SESSION['authorRole']) ? userHasRole('Content Editor') : false) ||
            (in_array("Site Administrator", $_SESSION['authorRole']) ? userHasRole('Site Administrator') : false))
    ) {
        $checkTextSize = isMobileDevice() ? 'p-3 text-3xl' : 'mx-1 p-2 text-sm';
        $buttonTextSize = isMobileDevice() ? 'text-3xl' : 'text-sm';
        $promptTextSize = isMobileDevice() ? 'text-3xl' : 'text-lg';
        $mutationForm = "
        <form action='?' method='post' class=' float-right inline-flex items-center '>
            <input type='hidden' name='ID' value='$IdeaID'>
            <Button type='submit' name='action' value='Edit' class=' float-right inline-flex items-center mr-2 $checkTextSize font-medium text-center text-white rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none bg-blue-600 hover:bg-blue-700 focus:ring-blue-800'>Edit</Button>
            <Button type='button' data-modal-toggle='$IdeaID' class=' float-right inline-flex items-center ml-2 $checkTextSize font-medium text-center text-white rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none bg-red-600 hover:bg-red-700 focus:ring-red-800'>Delete</Button>
        </form>

        <div id='$IdeaID' tabindex='-1' class='hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 md:inset-0 h-modal md:h-full'>
            <div class='relative p-4 w-full max-w-2xl h-full md:h-auto'>
                <div class='relative rounded-lg shadow bg-gray-700'>
                    <button type='button' class='absolute top-3 right-2.5 text-gray-400 bg-transparent rounded-lg text-sm p-1.5 ml-auto inline-flex items-center hover:bg-gray-800 hover:text-white' data-modal-toggle='$IdeaID'>
                        <svg aria-hidden='true' class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'><path fill-rule='evenodd' d='M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' clip-rule='evenodd'></path></svg>
                        <span class='sr-only'>Close modal</span>
                    </button>
                    <div class='p-6 text-center'>
                        <svg aria-hidden='true' class='mx-auto mb-4 w-14 h-14 text-gray-200' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                        <h3 class='mb-5 $promptTextSize font-normal text-gray-400'>Are you sure you want to delete this Idea?</h3>
                        <form action='?' method='post'>
                            <input type='hidden' name='ID' value='$IdeaID'>
                            <button data-modal-toggle='$IdeaID' type='submit' name='action' value='Delete' class='text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-800 font-medium rounded-lg $buttonTextSize inline-flex items-center px-5 py-2.5 text-center mr-2'>
                                Yes, I'm sure
                            </button>
                        <button data-modal-toggle='$IdeaID' type='button' class='focus:ring-4 focus:outline-none rounded-lg border $buttonTextSize font-medium px-5 py-2.5 focus:z-10 bg-gray-700 text-gray-300 border-gray-500 hover:text-white hover:bg-gray-600 focus:ring-gray-600'>No, cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        ";

        echo $mutationForm;
    } else {
        return false;
    }
}

function commentCheck($IdeaID, $currentURL)
{
    $IdeaID ? $IdeaID : '';
    $commentURL = "/?action=Comment&ideaID=$IdeaID";

    if ($currentURL == $commentURL) {
        return false;
    } else {
        $testSize = isMobileDevice() ? 'p-5 text-4xl' : 'p-3 text-sm';

        $commentHTML = "
        <a href='/?action=Comment&ideaID=$IdeaID' class='button'>
            <button type='submit' name='action' value='Comment' class='inline-flex items-center $testSize font-medium text-center text-white rounded-lg focus:ring-4 focus:outline-none bg-blue-600 hover:bg-blue-700 focus:ring-blue-800'>
                Comment
                <svg aria-hidden='true' class='ml-2 -mr-1 w-4 h-4' fill='currentColor' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'>
                    <path fill-rule='evenodd' d='M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z' clip-rule='evenodd'></path>
                </svg>
            </button>
        </a>
        ";
        echo $commentHTML;
    }
}

function votingSystem($upvote, $downvote, $NumVotes, $IdeaID)
{
    $upvote == "disabled" ? "disabled" : "";
    $downvote == "disabled" ? "disabled" : "";

    $votingSystemTextSize = isMobileDevice() ? 'text-4xl' : 'text-sm';
    $voteHTML = "
    <form action='' method='post'>
        <div>
            <button type='submit' value='true' name='downvote' class='inline-flex mr-1 float-right items-center p-1 $votingSystemTextSize font-medium text-center text-white rounded-lg focus:ring-4 focus:outline-none hover:bg-red-700 focus:ring-red-800 bg-gray-600 disabled:bg-red-600' $downvote>
                <svg xmlns='http://www.w3.org/2000/svg' width='1em' height='1em' preserveAspectRatio='xMidYMid meet' viewBox='0 0 24 24'>
                    <path fill='currentColor' d='M20.901 10.566A1.001 1.001 0 0 0 20 10h-4V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v7H4a1.001 1.001 0 0 0-.781 1.625l8 10a1 1 0 0 0 1.562 0l8-10c.24-.301.286-.712.12-1.059zM12 19.399L6.081 12H10V4h4v8h3.919L12 19.399z' />
                </svg>
            </button>
            <p class='inline-flex mr-1 float-right items-center p-1 mb-2 $votingSystemTextSize font-medium text-center text-white'>$NumVotes</p>
            <button type='submit' value='true' name='upvote' class='inline-flex mr-1 float-right items-center p-1 $votingSystemTextSize font-medium text-center text-white rounded-lg focus:ring-4 focus:outline-none hover:bg-blue-700 focus:ring-blue-800 bg-gray-600 disabled:bg-blue-600' $upvote>
                <svg xmlns='http://www.w3.org/2000/svg' width='1em' height='1em' preserveAspectRatio='xMidYMid meet' viewBox='0 0 24 24'>
                    <path fill='currentColor' d='M12.781 2.375c-.381-.475-1.181-.475-1.562 0l-8 10A1.001 1.001 0 0 0 4 14h4v7a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-7h4a1.001 1.001 0 0 0 .781-1.625l-8-10zM15 12h-1v8h-4v-8H6.081L12 4.601L17.919 12H15z' />
                </svg>
            </button>
            <input type='hidden' name='ID' value='$IdeaID'>
        </div>
    </form>
    ";

    return $voteHTML;
}
function votingCheck($IdeaID, $ideaVoteCounts, $totalIdeaVotes, $Votes)
{
    require_once $_SERVER['DOCUMENT_ROOT'] . '/views/auth/login/index.php';

    $authorID = $_SESSION['aid'];

    if (!userIsLoggedIn() || !$authorID) {
        echo votingSystem('', '', $Votes, $IdeaID);
    } else {
        $totalIdeaVotes ? $totalIdeaVotes : [];
        if (in_array($IdeaID, $totalIdeaVotes)) {
            foreach ($ideaVoteCounts as $ideaVoteCount) {
                if ($ideaVoteCount['IdeaID'] == $IdeaID) {
                    if ($ideaVoteCount['VoteNumber'] == 1) {
                        echo votingSystem('disabled', '', $Votes, $IdeaID);
                        break;
                    } elseif ($ideaVoteCount['VoteNumber'] == -1) {
                        echo votingSystem('', 'disabled', $Votes, $IdeaID);
                        break;
                    } else {
                        echo votingSystem('', '', $Votes, $IdeaID);
                        break;
                    }
                } else {
                    continue;
                }
            }
        } else {
            echo votingSystem('', '', $Votes, $IdeaID);
        }
    }
}
?>

<?php if (isset($ideas)) :
    foreach ($ideas as $Idea) : ?>
        <div class="flex <?php echo isMobileDevice() ? 'flex-row flex-wrap' : 'flex-col' ?> items-center justify-center">
            <div class="<?php echo isMobileDevice() ? 'flex min-w-[70%]' : 'w-96' ?> m-3 max-w-sm rounded-lg border shadow-md bg-gray-800 border-gray-700">
                <form method="get" action="?" class="<?php echo isMobileDevice() ? 'w-full' : '' ?>">
                    <a class="<?php echo isMobileDevice() ? 'w-full' : '' ?>" href="<?php html("/?action=Comment&ideaID=$Idea[ID]") ?>" class="button">
                        <button class="<?php echo isMobileDevice() ? 'w-full' : '' ?>" type="submit" name="action" value="Comment">
                            <img class="<?php echo isMobileDevice() ? 'w-full' : '' ?> rounded-t-lg" src="<?php echo str_contains($Idea['Image'], 'https://') ? $Idea['Image'] : "../assets/img/$Idea[Image]" ?>" alt="" />
                        </button>
                    </a>

                    <div class="mb-3">
                        <p class="mr-4 <?php echo isMobileDevice() ? 'text-3xl' : 'text-xs' ?>  float-right italic font-light text-gray-400"><?php html($Idea['IdeaDate']); ?></p>
                        <p class="ml-4 <?php echo isMobileDevice() ? 'text-3xl' : 'text-xs' ?> float-left font-normal text-gray-400">By <?php html($Idea['Name']); ?></p>
                    </div>

                    <div class=" <?php echo isMobileDevice() ? 'p-4 mt-8' : 'px-4 mt-4' ?>">
                        <h5 class="<?php echo isMobileDevice() ? 'mb-6 text-5xl' : 'mb-2 text-2xl' ?> font-bold tracking-tight text-white"><?php echo ($Idea['text']); ?></h5>
                        <?php commentCheck($Idea['ID'],  $_SERVER['REQUEST_URI']) ?>
                        <input type='hidden' name='ideaID' value='<?php echo ($Idea['ID']); ?>'>
                </form>
                <?php mutationCheck($Idea['ID'], $totalIdeas) ?>
                <?php votingCheck($Idea['ID'], $ideaVoteCounts, $totalIdeaVotes, $Idea['Vote']) ?>
            </div>
        </div>
<?php endforeach;
endif; ?>