<?php 
include __DIR__."/../vendor/autoload.php";

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


