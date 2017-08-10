<?php 
include __DIR__."/../vendor/autoload.php"; 

//////////////////// v5 /////////////////////////

#Conn->execute(STRING[,...args]);  //PDOStatement
#Conn->lastInsertId();             //int
#Conn->debug;                      //bool 
#Conn->scope();                    //Transaction
#Conn->sql(TABLE,[PK])             //#Conn[CLASS]   

#Model->where(STRING)          //self{table,where STRING...}
#Model->whereAnd(STRING)       //self{table,and STRING...}
#Model->whereOr(STRING)        //self{table,or STRING...}
#Model->order(STRING)          //self{table,order by STRING...}
#Model->field(FIELD)           //self{table,select FIELD...}
#Model->find([PK])             //self{table,where PK=...} 
#Model->limit(OFFSET)          //self{table,limit OFFSET... } 

#Model->all()                  //[...Model{table,row} ] 
#Model->all(FILED)             //[...VALUE]
#Model->all(FN)                //[...FN() ] 
#Model->keypair(KEY)           //[...KEY=>Model{table,row}  ]
#Model->keypair(KEY,FIELD)     //[...KEY=>VALUE ]
#Model->keypair(KEY,FN)        //[...KEY=>FN() ]
#Model->toArray()              //row  

#Model->val(KEY,VALUE)         //VOID  
#Model->val(REF,Model)         //VOID       
#Model->insert(ARRAY,...)      //Model  
#Model->update(ARRAY)          //RowCount 
#Model->delete(BOOL)           //RowCount  
#Model->replace(ARRAY)         //RowCount    
    
#Model->get(OFFSET)             #Model[OFFSET] //Model{table,row}   
#Model->load([PK])              #Model(PK...)  //Model{table,w,row}
#Model->sql(TABLE,[PK])         #Model[REF]    //Model{table,pk}   
#Model->ref(TABLE,[PK],[P=>F])  #Model[REF]    //Model{table,ref,pk}   
#Model->val(KEY)                #Model[KEY]    //VALUE or PK
#Model->count()
#Model->avg()
#Model->sum()
 
#Model[] = VALUE         > ERROR 
#Model[] = Model{table}  > insert TABLE  ^ 
#Model[] = Model{table}  > insert TABLE set TABLE.FK = Model.PK ^
#Model[] = Model{table}  > insert TABLE , Model[FK] = TABLE.PK  ^  
#Model[KEY] = VALUE      > Model[KEY] = VALUE ^
#Model[REF] = Model      > insert set REF.pk = Model.fk REF or update ^
#Model[REF] = Model      > update REF where REF.FK = Model.pk ^ 
#unset(Model[KEY])       > Model[KEY] = NULL ^ 
#unset(Model[REF])       > delete REF where REF.FK = Model.PK ^
#unset(Model[REF])       > delete REF where REF.PK = Model.FK ^  
  
 

//////////////////// v4 /////////////////////////

#Conn->execute(STRING[,...args]);     //PDOStatement
#Conn->lastInsertId();                //int
#Conn->debug;                         //bool 
#Conn->scope();                       //Transaction
#Conn->sql(TABLE,[...PK])             //Model{...}
   
#Model->get(INDEX)                    //Model{count=1}
#Model[INDEX]                         //Model{count=1}
#Model->load(...PK)                   //Model(count=1}
#Model(...Pk)                         //Model(count=1)
   
#Model->find(...PK)                   //self(where Pk and Pk ...)
#Model->limit(LIMIT,OFFSET)           //self{limit LIMIT offset OFFSET...}
#Model->where(STRING)                 //self{where STRING...}
#Model->whereAnd(STRING)              //self{and STRING...}
#Model->whereOr(STRING)               //self{or STRING...}
#Model->field(FIELD)                  //self{select FIELD...}
#Model->ref(TABLE,[...PK],[...REF])   //self{where REF in (TABLE.PK)...}
#Model[TABLE]                         //self{where REF in (TABLE.PK)...}
   
#Model->all()                         //[...Model{count=1}]
#Model->keypair()                     //[...PK=>Model{count=1}]
#Model->keypair(KEY)                  //[...KET=>Model{count=1}]
#Model->all(FIELD)                    //[...VALUE]
#Model->keypair(null,FIELD)           //[...PK=>VALUE]
#Model->keypair(KEY,FIELD)            //[...KET=>VALUE]

#Model->val()                         //PKV
#Model[NULL]                          //PKV
#Model->val(FIELD)                    //VALUE
#Model[FIELD]                         //VALUE
#Model->val(FIELD,VALUE)              //VALUE  changed
#Model[FIELD]=VALUE                   //VALUE  changed
   
#Model->update(ARRAY)      //RowCount
#Model->delete(BOOL)       //RowCount
#Model->replace(ARRAY)     //RowCount    
#Model->insert(ARRAY)      //Model


#Model->set(ARRAT)         //Model                非原子级操作(进程不安全(删除预定
#Model->save()             //self{unset(changed)} 非原子级操作(进程不安全(删除预定



///////////////////// v3 ////////////////////////

#Conn->execute(STRING[,...args]); #PDOStatement
#Conn->lastInsertId();            #int
#Conn->debug;                     #bool
#Conn->sql(TABLE,...PKS)          #SQL 
#Conn->scope();                   #Transaction

#SQL[INDEX]                 #Row/NULL
#SQL[FIELD]                 #mixed/NULL
#SQL[MODEL]                 #SQL/NULL
#SQL->get()                 #Row
#SQL->get(INDEX)            #Row 
#SQL->val()                 #array
#SQL->val(FIELD)            #mixed 
#SQL->ref(TABLE,PKS,REF)    #SQL 

#SQL(...PKV)                #ROW/NULL
#SQL->load(...PKV)          #ROW/Throw

#SQL->__call(AggregateFunction) #mixed
#SQL->count(FIELD)              #mixed
#SQL->sum(FIELD)                #mixed
#SQL->.....

#SQL->find(...PKV)          	  #SQL
#SQL->where(STRING[, ..._args]);  #SQL
#SQL->where(ARRAY);               #SQL
#SQL->and(STRING[, ..._args]);    #SQL
#SQL->and(ARRAY);                 #SQL
#SQL->or(STRING[, ..._args]);     #SQL
#SQL->or(ARRAY);                  #SQL
#SQL->order(STRING[,..._args]);   #SQL
#SQL->field(STRING);              #SQL
#SQL->limit(INT[,INT])            #SQL

#SQL->each(Model=>closer)   #void
#SQL->map(Model=>closer)    #array
#SQL->getIterator()         #iterator()=>Row
#SQL->getAllIterator()      #iterator()=>array
#SQL->all()                 #[array,array...]/[]  
#SQL->all(KEY)              #[VALUE,VALUE...]/[]  
#SQL->keypair(KEY)          #[KEY=>array,KEY=>array...]/[]
#SQL->keypair(KEY,VAL)      #[KEY=>VALUE,KEY=>VALUE...]/[] 

#SQL->insert(ARRAY);             #MODLE  
#SQL->insertMulit(ARRAY_LIST);   #int
#SQL->update(ARRAY);             #int
#SQL->delete([BOOL]);            #int 
#SQL->set(ARRAY);				 #SQL

#ROW[FIELD]                  #MIXED/NULL 
#ROW[MODEL]                  #Sql/NULL 
#ROW->val(FIELD)             #MIXED
#ROW->ref(TABLE,PKS,REF)     #SQL
#ROW->create()               #bool
#ROW->save([PKS])            #bool
#ROW->destroy([PKS])         #bool


# PHP <= 5.6 
# ERROR function and(){}
# ERROR function or(){} 


