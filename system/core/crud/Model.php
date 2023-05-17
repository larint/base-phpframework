<?php

class Model extends DBCRUD
{
    public function query($sql)
    {
        $data = [];
        if ($result = $this->conn->query($sql)) {
            $this->numRowsEffect = $result->num_rows;

            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $data[] = $row;
            }

            $result->free_result();
        }
        return arr_to_obj($data, false);
    }

}
