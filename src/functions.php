<?php

require_once "Compiler.php";
require_once "Commands/InstallMainSQLCommandline.php";
require_once "Commands/InstallMenuSQLCommandline.php";

require_once "Checks/Tables.php";
require_once "Checks/StoredProcedures.php";

require_once "Middlewares/Middleware.php";
require_once "Routes/JsLoader.php";
require_once "Routes/Query.php";
require_once "Routes/ApiUser.php";
require_once "Routes/SetupHandshake.php";
require_once "Routes/Ping.php";
require_once "Routes/Set.php";
require_once "Routes/Check.php";


require_once "Routes/Reset.php";

require_once "Routes/BCrypt.php";

require_once "Routes/Sounds.php";
require_once "Routes/Status.php";


require_once "Routes/counting/SkipStartBallotPaper.php";
require_once "Routes/counting/Save.php";

require_once "Routes/combine/Save.php";
require_once "Routes/combine/ReverseCheck.php";
require_once "Routes/combine/Reset.php";

require_once "Routes/pwgen/CreateColumns.php";
require_once "Routes/pwgen/DS.php";
require_once "Routes/pwgen/Import.php";
require_once "Routes/pwgen/TestColumn.php";
require_once "Routes/pwgen/BCrypt.php";

require_once "Routes/pwgen/Unique.php";
require_once "Routes/pwgen/SetPW.php";

require_once "Routes/wbimport/Upload.php";
require_once "Routes/wbimport/Process.php";


require_once "Routes/rescan/Insert.php";

require_once "Routes/images/BarcodeImage.php";

require_once "Routes/return/Setup.php";
require_once "Routes/return/Tan.php";
require_once "Routes/return/Save.php";

require_once "Routes/stacks/Open.php";
require_once "Routes/stacks/Cancle.php";


require_once "Routes/involvement/Reporting.php";
require_once "Routes/involvement/StatusImport.php";

require_once "Routes/export/AllData.php";
