import axios from 'axios';
export const api = axios.create({baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1',headers:{Accept:'application/json'}});
api.interceptors.request.use(config=>{const token=localStorage.getItem('sd_token'); if(token) config.headers.Authorization=`Bearer ${token}`; return config;});
export type User={id:number;name:string;email:string;role:string;company?:{name:string}};
export type Ticket={id:number;ticket_number:string;subject:string;description:string;status:string;priority:string;category:string;customer?:{name:string};assignee?:{name:string};created_at:string};
