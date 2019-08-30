<?php

namespace App\Exports;

use App\Http\Models\Workflow;
use App\Http\Models\Calls;
use App\Http\Models\GroupWorkflow;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class WorkflowExport implements FromQuery, WithMapping, WithHeadings, WithColumnFormatting, ShouldAutoSize 
{
   
    use Exportable;

    public function headings(): array
    {
        return [
            [
            	'Name Campaign',
            	'Type Campaign',
	            'Creation date',
	            'Total',
            ],            
        ];
    }

    public function map($workflow): array
    {

    	if($workflow->filter_type == 4 || $workflow->filter_type == 5){
    		$call = Calls::where('id_workflow', $workflow->id)->get();

    		if($workflow->filter_type == 4){
    			$type = "CALL";
    		}else{
    			$type = "CALL - SMS";
    		}
    		
	        $returnphone= [];
    		

    		$returnphone[0][0] = $workflow->name;
			$returnphone[0][1] = $type;
			$returnphone[0][2] = $workflow->created_at->format('d-m-Y');
			$returnphone[0][3] = $workflow->cost;
	        
	        $returnphone[1][0] = [];	            
	        
	        $returnphone[2][0] = 'Number';
	        $returnphone[2][1] = 'Status';
	        $returnphone[2][2] = 'Cost';

    		$i = 3;
			foreach($call as $calls) {
				
			    
			    $returnphone[$i][] = (int)$calls['phonenumber'];
			    $returnphone[$i][] = $calls['call_status'];
			    $returnphone[$i][] = (float)$calls['cost'];

			    $i++;
			   		    
			}

			if($workflow->filter_type == 5){
				$call = GroupWorkflow::where('id_workflow', $workflow->id)->select('destination_number', 'status_text', 'cost')->get();
    			
				foreach($call as $calls) {
					
				    
				    $returnphone[$i][] = (int)$calls['destination_number'];
				    $returnphone[$i][] = $calls['status_text'];
				    $returnphone[$i][] = (float)$calls['cost'];

				    $i++;
				   		    
				}


			}
			
    		return $returnphone;


    	}else{
    		
    		$call = GroupWorkflow::where('id_workflow', $workflow->id)->select('destination_number', 'status_text', 'cost')->get();
    		
    		
    		$returnphone= [];
    		

    		$returnphone[0][0] = $workflow->name;
			$returnphone[0][1] ='SMS';
			$returnphone[0][2] = $workflow->created_at->format('d-m-Y');
			$returnphone[0][3] = $workflow->cost;
	        
	        $returnphone[1][0] = [];	            
	        
	        $returnphone[2][0] = 'Number';
	        $returnphone[2][1] = 'Status';
	        $returnphone[2][2] = 'Cost';

    		$i = 3;
			foreach($call as $calls) {
				
			    
			    $returnphone[$i][] = (int)$calls['destination_number'];
			    $returnphone[$i][] = $calls['status_text'];
			    $returnphone[$i][] = (float)$calls['cost'];

			    $i++;
			   		    
			}

			
    		return $returnphone;
    	}
        
        
    }


    public function forId(int $id)
    {
        $this->id = $id;
        
        return $this;
    }

    public function query()
    {	
    	
        return Workflow::query()->where('id', $this->id);
        
    }


    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
            'A' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    
}
