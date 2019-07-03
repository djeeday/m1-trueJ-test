<?php
class PipeTest 
{
    //Company domain pipedrive
    private $urlBase = "https://a2cat-1ffecf.pipedrive.com/v1";
    //Api-token
    private $apiToken = "?api_token=e2c21c6ef8e4da7d09bcc728f1a1e6267d23ce57";
    //Var for storing notes - cause we call getNotesById() 3 times and can reduce 2 queries
    public $notesAray;
    
    /**
     * Gets all deals, if has notes yields it
     *
     * @return mixed
     */
    public function getDeals(){
        $url = $this->urlBase."/deals".$this->apiToken;
        $opts = array(
            'http'=>array(
              'method'=>"GET",
            )
          );
        $context = stream_context_create($opts);
        // gets json string using the HTTP headers set above
        $data =  file_get_contents($url, false, $context);
        //let's decode json
        $json = json_decode($data,true)["data"];
        $result = [];
        // Lets yield id, title, org, amount of deal and progress
        foreach($json as $k => $dealData){
            $result[$k]["id"] = $dealData["id"];
            $result[$k]["title"] = $dealData["title"];
            $result[$k]["stage_id"] = $dealData["stage_id"]."/6";
            $result[$k]["amount"] = $dealData["formatted_weighted_value"] ;
            $result[$k]["notes"] = (int) $dealData["notes_count"] > 0 ? $this->getNotesById($result[$k]["id"],'deal') : null;
        }
        
        return $result;
    }
    /**
     * Gets all persons, if has notes yields it
     *
     * @return mixed
     */
    public function getPersons(){
        $url = $this->urlBase."/persons".$this->apiToken;
        $opts = array(
            'http'=>array(
              'method'=>"GET",
            )
          );
        $context = stream_context_create($opts);
        // gets json string using the HTTP headers set above
        $data =  file_get_contents($url, false, $context);
        //let's decode json
        $json = json_decode($data,true)["data"];
        $result = [];
        // Lets yield id, name, org name, 1 phone, 1 email, date of next interaction and of course notes
        foreach($json as $k => $PersonData){
            $result[$k]["id"] = $PersonData["id"];
            $result[$k]["name"] = $PersonData["name"];
            $result[$k]["org_name"] = $PersonData["org_id"]["name"];
            $result[$k]["phone"] = $PersonData["phone"][0]["value"];
            $result[$k]["email"] = $PersonData["email"][0]["value"];
            //can be null
            $result[$k]["nextActivityDate"] = isset($PersonData["next_activity_date"]) ? $PersonData["next_activity_date"] : null;
            //can be null
            $result[$k]["notes"] = (int) $PersonData["notes_count"] > 0 ? $this->getNotesById($result[$k]["id"],'person') : null;
        }
        
        return $result;
    }
    /**
     * Gets all organizations, if has notes yields it
     *
     * @return mixed
     */
    public function getOrganizations(){
        $url = $this->urlBase."/organizations".$this->apiToken;
        $opts = array(
            'http'=>array(
              'method'=>"GET",
            )
          );
        $context = stream_context_create($opts);
        // gets json string using the HTTP headers set above
        $data =  file_get_contents($url, false, $context);
        //let's decode json
        $json = json_decode($data,true)["data"];
        $result = [];
        // Lets yield id, name, people_count, address_formatted_address and of course notes
        foreach($json as $k => $OrgData){
            $result[$k]["id"] = $OrgData["id"];
            $result[$k]["name"] = $OrgData["name"];
            $result[$k]["adress"] = $OrgData["address_formatted_address"];
            $result[$k]["people_count"] = $OrgData["people_count"];
            //can be null
            $result[$k]["notes"] = (int) $OrgData["notes_count"] > 0 ? $this->getNotesById($result[$k]["id"],'org') : null;
        }
        
        return $result;
    }
    public function getActivities(){
        $url = $this->urlBase."/activities".$this->apiToken;
        $opts = array(
            'http'=>array(
              'method'=>"GET",
            )
          );
        $context = stream_context_create($opts);
        // gets json string using the HTTP headers set above
        $data =  file_get_contents($url, false, $context);
        //let's decode json
        $json = json_decode($data,true)["data"];
        $result = [];
        // Lets yield some data and of course notes which is now in same dataset - $json
        foreach($json as $k => $ActivityData){
            $result[$k]["id"] = $ActivityData["id"];
            $result[$k]["done"] = $ActivityData["done"];
            $result[$k]["type"] = $ActivityData["type"];
            $result[$k]["due_date"] = $ActivityData["due_date"];
            $result[$k]["due_time"] = $ActivityData["due_time"];
            $result[$k]["duration"] = $ActivityData["duration"];
            $result[$k]["subject"] = $ActivityData["subject"];
            $result[$k]["location"] = $ActivityData["location"];
            $result[$k]["person_name"] = $ActivityData["person_name"];
            $result[$k]["org_name"] = $ActivityData["org_name"];
            $result[$k]["deal_title"] = $ActivityData["deal_title"];
            $result[$k]["notes"] = $ActivityData["note"];
        }
        
        return $result;
    }
    /**
     * Searches for notes to deals, persons or orgs
     *
     * @param [int] $id
     * @param [string] $type
     * @return mixed
     */
    public function getNotesById($id,$type){
      if (!isset($this->notesArray)){
        $url = $this->urlBase."/notes".$this->apiToken;
        $opts = array(
            'http'=>array(
              'method'=>"GET",
            )
          );
        $context = stream_context_create($opts);
        // gets json string using the HTTP headers set above
        $data =  file_get_contents($url, false, $context);
        //let's store array with decoded data
        $this->notesArray = json_decode($data,true)["data"];
      }
      $json = $this->notesArray;
      $result =[];
      for ($i=0;$i< count($json); $i++){
          if (isset($json[$i]["$type"."_id"])&& $json[$i]["$type"."_id"]  == $id){
              $result[] = $json[$i]["content"];
          }
      }
      return $result;
    }
}
