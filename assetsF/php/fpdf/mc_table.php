<?php
require('fpdf.php');

class PDF_MC_Table extends FPDF
{
    protected $widths;
    protected $aligns;
    protected $colorfondo;
    protected $linespace=5;

    protected $_x_;
    protected $_y_;

    function SetWidths($w){
        // Set the array of column widths
        $this->widths = $w;
    }

    function SetSpaceLine($spl){
        //Set the array of column widths
            $this->linespace=$spl;
    }

    function SetHeights($h){
        //Set the array of column widths
            $this->heights=$h;
    }

    function SetAligns($a){
        // Set the array of column alignments
        $this->aligns = $a;
    }

    function SetFondoColor($r,$g,$b){
        $this->SetFillColor($r,$g,$b);
    }

    function GuardaXY(){
        $this->_x_ = $this->GetX();
        $this->_y_ = $this->GetY();
    }

    function RecuperaXY(){
        $this->SetX($this->_x_);
        $this->SetY($this->_y_);
    }

    function RecuperaY(){ // Se debio guardar primero x y y con GuardaXY
        return $this->_y_;
    }

    function RecuperaX(){ // Se debio guardar primero x y y con GuardaXY
        return $this->_x_;
    }
    
    function Row($data,$_color_){
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h = ($this->linespace)*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            if ( isset($_color_) && $_color_ !== null ){
                $this->SetFillColor($_color_[0],$_color_[1],$_color_[2]);
                $this->Rect($x,$y,$w,$h,'F');// Con relleno cremosito
            }
            $this->Rect($x,$y,$w,$h);
            // Print the text
            $this->MultiCell($w,$this->linespace,$data[$i],0,$a);
            // Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        // Go to the next line
        $this->Ln($h);
    }
    // _________________________________________________________
    function RowSinCuadro($data){
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h = ($this->linespace)*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for($i=0;$i<count($data);$i++){
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //$this->Rect($x,$y,$w,$h);
            // Print the text
            if ($data[$i]=="_._"){
                $this->Line($this->GetX()+4, $this->GetY(), $this->GetX() + $w, $this->GetY());
            }elseif ($data[$i]=="_2.2_"){
                $x = $this->GetX();
                $y = $this->GetY();
                // Agregar dos líneas para simular una línea doble
                $this->Line($x+4, $y    , $x + $w, $y);
                $this->Line($x+4, $y + 1, $x + $w, $y + 1);
            }else{
              $this->MultiCell($w,$this->linespace,$data[$i],0,$a);  
            }
            // Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        // Go to the next line
        $this->Ln($h);
    }
    // _________________________________________________________
    function RowColor($aData_,$aColor_){
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($aData_);$i++){
            $nb = max($nb,$this->NbLines($this->widths[$i],$aData_[$i]));
        }
        $h = ($this->linespace)*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for($i=0;$i<count($aData_);$i++){
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border, pero primero el color de fondo
            if ( isset($aColor_)){
                $this->SetFillColor($aColor_[$i][0],$aColor_[$i][1],$aColor_[$i][2]);
                $this->Rect($x,$y,$w,$h,'F');// Con relleno cremosito
            }
            $this->Rect($x,$y,$w,$h);
            // Print the text
            $this->MultiCell($w,$this->linespace,$aData_[$i],0,$a);
            // Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        // Go to the next line
        $this->Ln($h);
    }
    // _________________________________________________________
    function CheckPageBreak($h){
        // If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt){
        // Compute the number of lines a MultiCell of width w will take
        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = $this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',(string)$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
    /*
        x, y: esquina superior izquierda del rectángulo. 
        w, h: ancho y alto. 
        r: radio de las esquinas redondeadas. 
        corners: número de las esquinas a redondear: 1, 2, 3, 4 o cualquier combinación (1=arriba a la izquierda, 2=arriba a la derecha, 3=abajo a la derecha, 4=abajo a la izquierda). 
        style: igual que Rect(): F, D (predeterminado), FD o DF. 
    */
    function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = ''){
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        if (strpos($corners, '2')===false)
            $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
        else
            $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        if (strpos($corners, '3')===false)
            $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        if (strpos($corners, '4')===false)
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        if (strpos($corners, '1')===false)
        {
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
            $this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k ));
        }
        else
            $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3){
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}
?>
