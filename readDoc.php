<?php
    session_start();
    if(empty($_SESSION['username'])){
        header("location: login.php");
    }
    include('./includes/configs/DBconfig.php');
    $page_title='Read Document | Document Management System';
    include('./includes/pages/header.php');
    $doc_id=$_GET['docid'];
    $read=mysqli_query($con,"SELECT * FROM documents WHERE doc_id='$doc_id'");
    while($doc=mysqli_fetch_assoc($read)){
        $size=$doc['size'];
        $formatSize='';
        if($size >= 419430400) {
             $formatSize=number_format($size / 419430400, 2) . ' MB';
        } elseif    ($size >= 1024) {
             $formatSize=number_format($size / 1024, 2) . ' KB';
        } else {
             $size . ' bytes';
        }

        $status_view='';
        if($doc['status']=='Pending'){
            $status_view="
                <div class='pending' style='width: 100px'>". $doc['status'] ."</div>
            ";
        }
        else if($doc['status']=='Success'){
            $status_view="
                 <div class='Success' style='width: 100px'>". $doc['status'] ."</div>
             ";
        }

        if(isset($_GET['msg'])){
            ?>
                        
                        <div class="msg-container">
                            <?php
                                if(isset($_GET['msg'])){
                            ?>
                            <div class="msg">
                                <div class="msg-header">
                                    <h2>Message</h2>
                                    <button onclick="CloseMSG()"><i class="fas fab fa-times"></i></button>
                                </div>
                                <div class="msg-body">
                                    <p><?php echo  $_GET['msg'] ?></p>
                                </div>
                            </div>
                            <?php
                                }
            
                                if(isset($_GET['error'])){
                            ?>
                            <div class="msg">
                                <div class="msg-header">
                                    <h2>Error</h2>
                                    <button onclick="CloseMSG()"><i class="fas fab fa-times"></i></button>
                                </div>
                                <div class="msg-body">
                                    <p><?php echo  $_GET['error'] ?></p>
                                </div>
                            </div>
                            <?php
                                }
                            ?>
                        </div>
                        
                        <?php
                            }
                        ?>

    <div class="read">
        <div class="details">
            <div class="doc-details">
                <h1><i class="fas fab fa-file-pdf"></i> PDF: <?php echo $doc['doc_name'] ?></h1>
                <br>
                <p>Date: <?php echo $doc['created_at'] ?></p>
                <p>Size: <?php echo $formatSize ?></p>
                <br>
                <p>Status:<?php echo $status_view ?></p>
                <button onclick='OpenPrivew()' class='button'>Read Document</button>
            </div>


        <?php
            if($doc['status']=="Pending"){
        ?>

        <div class="form">
            <div class="form-header">
                <h2>Add Agreement</h2>
            </div>
            <form action="" method="post">
                <input type="text" name="seller_name" placeholder="Seller Name" required>
                <input type="text" name="buyer_name" placeholder="Buyer Name" required>
                <input type="text" name="upi" placeholder="UPI" required>
                <input type="hidden" name="doc_id" value="<?php echo $doc_id ?>">
                <div class="btn">
                    <button type="submit" name="add_agreement">Add Agreement</button>
                </div>
            </form>
        </div>

        <?php

                if(isset($_POST['add_agreement'])){
                    $seller_name=$_POST['seller_name'];
                    $buyer_name=$_POST['buyer_name'];
                    $upi=$_POST['upi'];
                    // $type=$_POST['type'];
                    $doc_id=$_POST['doc_id'];

                    $status='Success';

                    $add=mysqli_query($con,"INSERT INTO agreement VALUES('NULL', '$seller_name', '$buyer_name', '$upi', '$doc_id', NOW(), NOW())");
                    $update=mysqli_query($con,"UPDATE documents SET status='$status' WHERE doc_id='$doc_id'");
                    if($add && $update){
                        $msg="Agreement added successfully";
                        header('location: readDoc.php?msg=' . $msg . '&&docid=' . $doc_id);
                    }
                    else{
                        $msg="Failed to add agreement";
                        header('location: readDoc.php?error=' . $msg . '&&docid=' . $doc_id);
                    }
                }

            }
            else if($doc['status']=="Success"){
                $agreement=mysqli_query($con,"SELECT * FROM agreement WHERE doc_id='$doc_id'");
                while($agree=mysqli_fetch_assoc($agreement)){
        ?>
            <div class="agree">
                <h2>Document Agreements</h2>
                <div class="dt">
                    <h2>Seller: <?php echo $agree['seller_name'] ?></h2>
                    <h2>Buyer: <?php echo $agree['buyer_name'] ?></h2>
                    <h2>UPI N&deg;: <?php echo $agree['upi'] ?></h2>
                </div>
            </div>
        <?php
                }
            }
        ?>

</div>
        <div class="doc">
            <div class="header">
                <h2>Read Document</h2>
                <button onclick="ClosePreview()"><i class="fas fab fa-times"></i></button>
            </div>
          <embed src="<?php echo $doc['file_path'] ?>" type="application/pdf" />
        </div>
    </div>

<?php
   }
    include('./includes/pages/footer.php');
?>