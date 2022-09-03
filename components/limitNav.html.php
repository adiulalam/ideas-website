<form method="get" action="">
    <div class="flex flex-col my-auto items-center bgimg bg-cover p-3">
        <div class="inline-flex">
            <button name='prevPage' value="<?php echo $offset ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l">
                Prev
            </button>
            <button name='nextPage' value="<?php echo $offset ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r">
                Next
            </button>
        </div>
    </div>
</form>