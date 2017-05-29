<?php 
include __DIR__."/../vendor/autoload.php";

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
#Model->val(FIELD)                    //VALUE
#Model[FIELD]                         //VALUE
#Model->val(FIELD,VALUE)              //VALUE  changed
#Model[FIELD]=VALUE                   //VALUE  changed
   
#Model->insert()      //Model
#Model->update()      //RowCount
#Model->delete()      //RowCount
#Model->save()        //self{unset(changed)}
#Model->set()         //Model



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


